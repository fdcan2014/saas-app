<?php
namespace Modules\Purchase\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Purchase\Services\PurchaseService;
use Modules\Core\Services\ContextService;

/**
 * Controller REST para recursos de compras.
 */
class PurchaseController extends ResourceController
{
    protected $format = 'json';
    protected PurchaseService $service;

    public function __construct()
    {
        $this->service = new PurchaseService(
            new \Modules\Purchase\Repositories\PurchaseRepository(),
            new \Modules\Product\Repositories\ProductRepository(),
            new \Modules\Supplier\Repositories\SupplierRepository()
        );
    }

    /**
     * Lista compras do tenant atual.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        $purchases = $this->service->list($tenantId);
        return $this->respond($purchases);
    }

    /**
     * Mostra compra específica.
     */
    public function show($id = null)
    {
        $tenantId = ContextService::getTenantId();
        $purchase = $this->service->get((int) $id, $tenantId);
        if (! $purchase) {
            return $this->failNotFound('Compra não encontrada');
        }
        return $this->respond($purchase);
    }

    /**
     * Cria uma nova compra.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        try {
            $data = $this->request->getJSON(true) ?? [];
            $purchase = $this->service->create($tenantId, $data);
            return $this->respondCreated($purchase);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }

    /**
     * Atualiza o status de uma compra.
     */
    public function update($id = null)
    {
        $tenantId = ContextService::getTenantId();
        $status   = $this->request->getJSON(true)['status'] ?? null;
        if (! $status) {
            return $this->failValidationErrors('Status não informado');
        }
        $purchase = $this->service->updateStatus((int) $id, $tenantId, $status);
        if (! $purchase) {
            return $this->failNotFound('Compra não encontrada');
        }
        return $this->respond($purchase);
    }

    /**
     * Remove uma compra.
     */
    public function delete($id = null)
    {
        $tenantId = ContextService::getTenantId();
        $result   = $this->service->delete((int) $id, $tenantId);
        if (! $result) {
            return $this->failNotFound('Compra não encontrada');
        }
        return $this->respondDeleted(['status' => 'ok']);
    }
}