<?php
namespace Modules\Payment\Services;

use Modules\Payment\Repositories\PaymentRepository;
use Modules\Payment\Entities\PaymentEntity;
use Modules\Order\Repositories\OrderRepository;
use RuntimeException;

/**
 * Serviço que encapsula a lógica de pagamentos de pedidos.
 */
class PaymentService
{
    protected PaymentRepository $paymentRepo;
    protected OrderRepository $orderRepo;

    public function __construct(PaymentRepository $paymentRepo, OrderRepository $orderRepo)
    {
        $this->paymentRepo = $paymentRepo;
        $this->orderRepo   = $orderRepo;
    }

    /**
     * Lista pagamentos do tenant e opcionalmente de um pedido específico.
     *
     * @return PaymentEntity[]
     */
    public function list(int $tenantId, ?int $orderId = null): array
    {
        return $this->paymentRepo->findByTenant($tenantId, $orderId);
    }

    /**
     * Cria um pagamento para um pedido.
     *
     * Valida se o pedido existe e pertence ao tenant, verifica valor positivo
     * e registra o pagamento. Não altera status do pedido aqui.
     */
    public function create(int $tenantId, array $data): PaymentEntity
    {
        $orderId = $data['order_id'] ?? null;
        if (! $orderId) {
            throw new RuntimeException('order_id é obrigatório');
        }
        $order = $this->orderRepo->find($orderId, $tenantId);
        if (! $order) {
            throw new RuntimeException('Pedido não encontrado para este tenant');
        }
        $amount = isset($data['amount']) ? (float) $data['amount'] : 0.0;
        if ($amount <= 0) {
            throw new RuntimeException('Valor do pagamento deve ser maior que zero');
        }
        $paymentData = [
            'tenant_id'  => $tenantId,
            'order_id'   => $orderId,
            'amount'     => $amount,
            'method'     => $data['method'] ?? null,
            'status'     => $data['status'] ?? 'pending',
            'paid_at'    => $data['paid_at'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $payment = $this->paymentRepo->create($paymentData);
        // Se o status já estiver marcado como pago, recalcula status do pedido
        if ($payment->status === 'paid') {
            $this->recalculateOrderStatus($tenantId, $orderId);
        }
        return $payment;
    }

    /**
     * Atualiza um pagamento existente (ex. marca como pago).
     */
    public function update(int $id, int $tenantId, array $data): ?PaymentEntity
    {
        $updateData = [];
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        if (isset($data['paid_at'])) {
            $updateData['paid_at'] = $data['paid_at'];
        }
        if (empty($updateData)) {
            throw new RuntimeException('Nada para atualizar');
        }
        $payment = $this->paymentRepo->update($id, $tenantId, $updateData);
        if ($payment && isset($updateData['status']) && $updateData['status'] === 'paid') {
            // Recalcula status do pedido após pagamento
            $this->recalculateOrderStatus($tenantId, $payment->order_id);
        }
        return $payment;
    }

    /**
     * Após criar ou atualizar um pagamento, recalcula o status do pedido baseado nos pagamentos realizados.
     *
     * Se o valor total pago for igual ou superior ao valor do pedido, define o status como 'paid';
     * se for maior que zero e menor que o total, define como 'partial'; caso contrário mantém 'pending'.
     */
    public function recalculateOrderStatus(int $tenantId, int $orderId): void
    {
        // Obtém total pago
        $totalPaid = $this->paymentRepo->sumPaidAmount($tenantId, $orderId);
        // Obtém pedido
        $order = $this->orderRepo->find($orderId, $tenantId);
        if (! $order) {
            return;
        }
        $orderTotal = $order->total;
        $newStatus  = $order->status;
        if ($totalPaid >= $orderTotal && $orderTotal > 0) {
            $newStatus = 'paid';
        } elseif ($totalPaid > 0 && $totalPaid < $orderTotal) {
            $newStatus = 'partial';
        } elseif ($totalPaid == 0) {
            $newStatus = 'pending';
        }
        if ($newStatus !== $order->status) {
            $this->orderRepo->update($orderId, $tenantId, ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }
}