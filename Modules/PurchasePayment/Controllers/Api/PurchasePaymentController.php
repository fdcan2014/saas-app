<?php
namespace Modules\PurchasePayment\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\PurchasePayment\Services\PurchasePaymentService;
use Modules\Core\Services\ContextService;

/**
 * Controlador REST para pagamentos de compras.
 */
class PurchasePaymentController extends ResourceController
{
    protected $format = 'json';
    protected PurchasePaymentService $service;

    public function __construct()
    {
        $this->service = new PurchasePaymentService(
            new \Modules\PurchasePayment\Repositories\PurchasePaymentRepository(),
            new \Modules\Purchase\Repositories\PurchaseRepository()
        );
    }

    /**
     * Lista pagamentos de compras do tenant, podendo filtrar por purchase_id via query string.
     */
    public function index()
    {
        $tenantId   = ContextService::getTenantId();
        $purchaseId = $this->request->getGet('purchase_id');
        $purchaseId = $purchaseId !== null ? (int) $purchaseId : null;
        $payments   = $this->service->list($tenantId, $purchaseId);
        return $this->respond($payments);
    }

    /**
     * Cria um pagamento para uma compra.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        try {
            $data = $this->request->getJSON(true) ?? [];
            $payment = $this->service->create($tenantId, $data);
            return $this->respondCreated($payment);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }

    /**
     * Atualiza um pagamento (por exemplo, para marcar como pago).
     */
    public function update($id = null)
    {
        $tenantId = ContextService::getTenantId();
        try {
            $data = $this->request->getJSON(true) ?? [];
            $payment = $this->service->update((int) $id, $tenantId, $data);
            if (! $payment) {
                return $this->failNotFound('Pagamento de compra não encontrado');
            }
            return $this->respond($payment);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }
}