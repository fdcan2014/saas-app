<?php
namespace Modules\Core\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class TenantContextFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $tid = $request->getHeaderLine('X-Tenant-Id');
        if ($tid !== '') {
            try {
                $ctx = service('Modules\Core\Services\ContextService');
                if (method_exists($ctx, 'setTenantId')) {
                    $ctx->setTenantId((int)$tid);
                }
            } catch (\Throwable $e) {}
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
