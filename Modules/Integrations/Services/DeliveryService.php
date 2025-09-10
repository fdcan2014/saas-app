<?php
namespace Modules\Integrations\Services;

use Modules\Integrations\Repositories\WebhookRepository;
use Modules\Integrations\Repositories\OutboxRepository;
use Modules\Integrations\Helpers\Signer;
use Modules\Core\Services\CryptoService;
use Modules\Integrations\Config\Integrations;

class DeliveryService
{
    protected \CodeIgniter\Database\ConnectionInterface $db;
    public function __construct(
        protected WebhookRepository $webhooks,
        protected OutboxRepository $outbox,
        protected CryptoService $crypto,
        protected Integrations $cfg
    ) {
        $this->db = \Config\Database::connect();
    }

    public function runBatch(int $limit = null): int
    {
        $limit = $limit ?? $this->cfg->workerBatch;
        $now = date('Y-m-d H:i:s');
        // Buscar mensagens pendentes prontas para tentar
        $rows = $this->db->table('outbox_messages')
            ->where('status', 'pending')
            ->groupStart()
                ->where('next_attempt_at IS NULL', null, false)
                ->orWhere('next_attempt_at <=', $now)
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->get()->getResultArray();

        $processed = 0;
        foreach ($rows as $msg) {
            $okAll = $this->deliverOne((int)$msg['tenant_id'], (int)$msg['id'], $msg['event'], $msg['payload_json'], (int)$msg['attempts']);
            if ($okAll) {
                $this->db->table('outbox_messages')->where('id', $msg['id'])->update(['status' => 'delivered', 'updated_at' => date('c')]);
            } else {
                $attempts = ((int)$msg['attempts']) + 1;
                $backoff = min($this->cfg->backoffCap, $this->cfg->backoffBase * (2 ** max(0, $attempts - 1)));
                // jitter +/- 20%
                $jitter = (int)($backoff * (0.8 + (mt_rand() / mt_getrandmax()) * 0.4));
                $next = date('Y-m-d H:i:s', time() + $jitter);
                $update = ['attempts' => $attempts, 'next_attempt_at' => $next, 'updated_at' => date('c')];
                if ($attempts >= $this->cfg->maxAttempts) {
                    $update['status'] = 'failed';
                }
                $this->db->table('outbox_messages')->where('id', $msg['id'])->update($update);
            }
            $processed++;
        }
        return $processed;
    }

    /** Entrega uma mensagem para todos os webhooks do evento; retorna true se TODOS entregues com 2xx */
    protected function deliverOne(int $tenantId, int $outboxId, string $event, string $payloadJson, int $attempt): bool
    {
        $hooks = $this->webhooks->listByTenant($tenantId);
        $hooks = array_values(array_filter($hooks, fn($h) => ($h['event'] ?? '') === $event && (int)($h['active'] ?? 0) === 1));
        if (empty($hooks)) {
            // Nada para entregar — considerar entregue.
            return true;
        }

        $allOk = true;
        foreach ($hooks as $h) {
            $secret = null;
            if (!empty($h['secret_enc'])) {
                try { $secret = $this->crypto->decrypt($h['secret_enc']); } catch (\Throwable $e) { $secret = null; }
            }
            $ts = time();
            $headers = [
                'Content-Type: application/json',
                'User-Agent: ' . $this->cfg->userAgent,
                'X-Webhook-Event: ' . $event,
                'X-Webhook-Id: ' . $outboxId,
                'X-Webhook-Timestamp: ' . $ts,
            ];
            if ($secret) {
                $headers[] = 'X-Webhook-Signature: ' . Signer::sign($secret, $payloadJson, $ts);
            }

            [$ok, $http, $dur, $resp, $err] = $this->postJson($h['url'], $payloadJson, $headers);
            $this->db->table('webhook_deliveries')->insert([
                'tenant_id' => $tenantId,
                'webhook_id'=> $h['id'],
                'outbox_id' => $outboxId,
                'status'    => $ok ? 'success' : 'failed',
                'http_status' => $http,
                'duration_ms' => $dur,
                'response_snippet' => $resp !== null ? mb_substr($resp, 0, 255) : null,
                'error' => $err,
                'attempt' => max(1, $attempt + 1),
                'delivered_at' => $ok ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$ok) { $allOk = false; }
        }
        return $allOk;
    }

    /** Faz POST JSON com cURL e retorna [ok(bool), http_code(int|null), duration_ms(int), body_snippet(?string), error(?string)] */
    protected function postJson(string $url, string $body, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->cfg->httpTimeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $start = microtime(true);
        $resp = curl_exec($ch);
        $dur = (int)round((microtime(true) - $start) * 1000);
        if ($resp === false) {
            $err = curl_error($ch);
            $http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: null;
            curl_close($ch);
            return [false, $http, $dur, null, $err];
        }
        $http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        $ok = $http >= 200 and $http < 300
            or ($http == 410); // 410 Gone — pode considerar entregue para parar
        return [$ok, $http, $dur, $resp, ($ok ? null : ('HTTP ' . $http))];
    }
}
