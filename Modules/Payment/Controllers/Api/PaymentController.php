<?php
namespace Modules\Payment\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Payment\Services\PaymentService;
use Modules\Core\Services\ContextService;

/**
 * Controller REST para pagamentos de pedidos.
 */
class PaymentController extends ResourceController
{
    protected $format = 'json';
    protected PaymentService $service;

    public function __construct()
    {
        $this->service = new PaymentService(
            new \Modules\Payment\Repositories\PaymentRepository(),
            new \Modules\Order\Repositories\OrderRepository()
        );
    }

    /**
     * Lista pagamentos do tenant atual. Pode filtrar por order_id via query string.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        $orderId  = $this->request->getGet('order_id');
        $orderId  = $orderId !== null ? (int) $orderId : null;
        $payments = $this->service->list($tenantId, $orderId);
        return $this->respond($payments);
    }

    /**
     * Cria um pagamento.
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
                return $this->failNotFound('Pagamento não encontrado');
            }
            return $this->respond($payment);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }
}