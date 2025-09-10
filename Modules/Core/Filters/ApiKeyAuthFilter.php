<?php
namespace Modules\Core\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Modules\Integrations\Services\ApiKeyAuthService;

class ApiKeyAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $hdr = $request->getHeaderLine('Authorization');
        $bearer = null;
        if (str_starts_with(strtolower($hdr), 'bearer ')) {
            $bearer = trim(substr($hdr, 7));
        }
        $svc = new ApiKeyAuthService();
        $apiKey = $svc->findByPlaintext($bearer);
        if (!$apiKey) {
            return service('response')->setStatusCode(401)->setJSON(['error'=>'invalid_api_key']);
        }

        // Attach to request attributes via headers (non-standard, but accessible)
        $request->setHeader('X-ApiKey-Id', (string)$apiKey['id']);
        $request->setHeader('X-ApiKey-TenantId', (string)$apiKey['tenant_id']);
        $request->setHeader('X-ApiKey-Scopes', json_encode($apiKey['scopes'] ?? []));

        // If ContextService exists, set tenant
        try {
            $ctx = service('Modules\Core\Services\ContextService');
            if (method_exists($ctx, 'setTenantId')) {
                $ctx->setTenantId((int)$apiKey['tenant_id']);
            }
            if (method_exists($ctx, 'setApiKey')) {
                $ctx->setApiKey($apiKey);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
