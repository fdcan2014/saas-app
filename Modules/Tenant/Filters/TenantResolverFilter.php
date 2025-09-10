<?php
namespace Modules\Tenant\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Modules\Tenant\Services\TenantService;
use Modules\Core\Services\ContextService;

/**
 * Filtro que resolve o tenant para cada requisição e armazena o contexto.
 */
class TenantResolverFilter implements FilterInterface
{
    protected TenantService $tenants;

    public function __construct(TenantService $tenants)
    {
        $this->tenants = $tenants;
    }

    /**
     * Antes da requisição: identifica o tenant a partir do host ou header
     * e armazena em algum local acessível (ex.: serviço de contexto).
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $host = $request->getServer('HTTP_HOST');
        $header = $request->getHeaderLine('X-Tenant-ID');
        $tenant = null;
        if ($header) {
            // Header tem prioridade
            $tenant = $this->tenants->resolveTenant($header);
        } else {
            $tenant = $this->tenants->resolveTenant($host);
        }
        if ($tenant) {
            // Armazena o tenant resolvido no contexto global para que outros
            // componentes possam acessar o tenant durante a requisição.
            ContextService::setTenant($tenant);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada a fazer após a requisição
    }
}