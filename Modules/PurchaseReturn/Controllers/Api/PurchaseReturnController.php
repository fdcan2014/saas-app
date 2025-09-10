<?php
namespace Modules\PurchaseReturn\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\PurchaseReturn\Services\PurchaseReturnService;
use Modules\Core\Services\ContextService;

/**
 * Controlador REST para devoluções de compras.
 */
class PurchaseReturnController extends ResourceController
{
    protected $format = 'json';
    protected PurchaseReturnService $service;

    public function __construct()
    {
        $this->service = new PurchaseReturnService(
            new \Modules\PurchaseReturn\Repositories\PurchaseReturnRepository(),
            new \Modules\Product\Repositories\ProductRepository(),
            new \Modules\Purchase\Repositories\PurchaseRepository()
        );
    }

    /**
     * Lista devoluções de compras do tenant.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        $returns  = $this->service->list($tenantId);
        return $this->respond($returns);
    }

    /**
     * Exibe detalhes de uma devolução de compra.
     */
    public function show($id = null)
    {
        $tenantId = ContextService::getTenantId();
        $return   = $this->service->get((int) $id, $tenantId);
        if (! $return) {
            return $this->failNotFound('Devolução de compra não encontrada');
        }
        return $this->respond($return);
    }

    /**
     * Cria uma devolução de compra.
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