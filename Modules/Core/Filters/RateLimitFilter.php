<?php
namespace Modules\Core\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Modules\Core\Config\RateLimit as RateCfg;

class RateLimitFilter implements FilterInterface
{
    protected \CodeIgniter\Database\ConnectionInterface $db;
    protected RateCfg $cfg;
    protected ?\Redis $redis = null;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cfg = new RateCfg();
        $this->initRedis();
    }

    protected function initRedis(): void
    {
        $url = getenv('REDIS_URL') ?: null;
        if (!$url) return;
        if (!class_exists('Redis')) return;
        $parts = parse_url($url);
        if (!$parts || !isset($parts['host'])) return;
        $r = new \Redis();
        $r->connect($parts['host'], $parts['port'] ?? 6379, 1.5);
        if (isset($parts['pass'])) $r->auth($parts['pass']);
        if (isset($parts['path'])) $r->select((int)trim($parts['path'], '/'));
        $this->redis = $r;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $key = $this->resolveKey($request);
        if (!$key) return null; // no key -> skip

        $now = time();
        $window = $this->cfg->window;
        $bucket = (int)floor($now / $window);

        if ($this->redis) {
            $redisKey = "rl:{$key}:{$bucket}";
            $count = (int)$this->redis->incr($redisKey);
            if ($count === 1) $this->redis->expire($redisKey, $window);
            $remaining = max(0, $this->cfg->limit - $count);
            if ($count > $this->cfg->limit) {
                return service('response')->setStatusCode(429)->setJSON(['error'=>'rate_limited']);
            }
            service('response')->setHeader($this->cfg->headerLimit, (string)$this->cfg->limit);
            service('response')->setHeader($this->cfg->headerRemaining, (string)$remaining);
            service('response')->setHeader($this->cfg->headerReset, (string)(($bucket+1)*$window));
            return null;
        }

        // Fallback DB (simplified fixed window)
        $row = $this->db->table('api_key_rate_limits')
            ->where(['rate_key' => $key, 'bucket' => $bucket])
            ->get()->getRowArray();

        if (!$row) {
            $this->db->table('api_key_rate_limits')->insert([
                'rate_key' => $key, 'bucket' => $bucket, 'count' => 1, 'updated_at'=>date('c')
            ]);
            $remaining = $this->cfg->limit - 1;
        } else {
            $count = ((int)$row['count']) + 1;
            $this->db->table('api_key_rate_limits')->where(['rate_key'=>$key, 'bucket'=>$bucket])
                ->update(['count' => $count, 'updated_at'=>date('c')]);
            $remaining = max(0, $this->cfg->limit - $count);
            if ($count > $this->cfg->limit) {
                return service('response')->setStatusCode(429)->setJSON(['error'=>'rate_limited']);
            }
        }
        service('response')->setHeader($this->cfg->headerLimit, (string)$this->cfg->limit);
        service('response')->setHeader($this->cfg->headerRemaining, (string)$remaining);
        service('response')->setHeader($this->cfg->headerReset, (string)(($bucket+1)*$window));
        return null;
    }

    protected function resolveKey(RequestInterface $request): ?string
    {
        // Prefer API key
        $aid = $request->getHeaderLine('X-ApiKey-Id');
        if ($aid !== '') return 'api:' . $aid;

        // If authenticated user exists, try user id
        $uid = null;
        try {
            $auth = service('authentication');
            if ($auth && method_exists($auth, 'id')) {
                $uid = $auth->id();
            }
        } catch (\Throwable $e) {}

        if ($uid) return 'user:' . $uid;

        // else use ip
        return 'ip:' . $request->getIPAddress();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
