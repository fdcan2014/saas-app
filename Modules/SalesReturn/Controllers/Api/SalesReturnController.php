<?php
namespace Modules\SalesReturn\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\SalesReturn\Services\SalesReturnService;
use Modules\Core\Services\ContextService;

/**
 * Controlador REST para devoluções de vendas.
 */
class SalesReturnController extends ResourceController
{
    protected $format = 'json';
    protected SalesReturnService $service;

    public function __construct()
    {
        $this->service = new SalesReturnService(
            new \Modules\SalesReturn\Repositories\SalesReturnRepository(),
            new \Modules\Product\Repositories\ProductRepository(),
            new \Modules\Order\Repositories\OrderRepository()
        );
    }

    /**
     * Lista devoluções de vendas do tenant.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        $returns  = $this->service->list($tenantId);
        return $this->respond($returns);
    }

    /**
     * Exibe detalhes de uma devolução.
     */
    public function show($id = null)
    {
        $tenantId = ContextService::getTenantId();
        $return   = $this->service->get((int) $id, $tenantId);
        if (! $return) {
            return $this->failNotFound('Devolução não encontrada');
        }
        return $this->respond($return);
    }

    /**
     * Cria uma devolução de venda.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        try {
            $data = $this->request->getJSON(true) ?? [];
            $return = $this->service->create($tenantId, $data);
            return $this->respondCreated($return);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }
}