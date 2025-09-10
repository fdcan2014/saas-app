<?php
namespace Modules\Shipping\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Shipping\Services\ShippingService;
use Modules\Core\Services\ContextService;

/**
 * Controller REST para gestão de envios.
 */
class ShippingController extends ResourceController
{
    protected $format = 'json';
    protected ShippingService $service;

    public function __construct()
    {
        $this->service = new ShippingService(
            new \Modules\Shipping\Repositories\ShippingRepository(),
            new \Modules\Order\Repositories\OrderRepository()
        );
    }

    /**
     * Lista envios do tenant, podendo filtrar por order_id via query string.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        $orderId  = $this->request->getGet('order_id');
        $orderId  = $orderId !== null ? (int) $orderId : null;
        $shippings = $this->service->list($tenantId, $orderId);
        return $this->respond($shippings);
    }

    /**
     * Exibe um envio específico.
     */
    public function show($id = null)
    {
        $tenantId = ContextService::getTenantId();
        $shipping = $this->service->get((int) $id, $tenantId);
        if (! $shipping) {
            return $this->failNotFound('Envio não encontrado');
        }
        return $this->respond($shipping);
    }

    /**
     * Cria um novo envio para um pedido.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        try {
            $data = $this->request->getJSON(true) ?? [];
            $shipping = $this->service->create($tenantId, $data);
            return $this->respondCreated($shipping);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }

    /**
     * Atualiza um envio existente.
     */
    public function update($id = null)
    {
        $tenantId = ContextService::getTenantId();
        try {
            $data = $this->request->getJSON(true) ?? [];
            $shipping = $this->service->update((int) $id, $tenantId, $data);
            if (! $shipping) {
                return $this->failNotFound('Envio não encontrado');
            }
            return $this->respond($shipping);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }
}