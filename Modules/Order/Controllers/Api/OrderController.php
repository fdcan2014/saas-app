<?php
namespace Modules\Order\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Order\Services\OrderService;
use Modules\Core\Services\ContextService;
use RuntimeException;

/**
 * Controlador REST para gestão de pedidos (vendas).
 */
class OrderController extends ResourceController
{
    protected OrderService $orders;

    public function __construct(OrderService $orders)
    {
        $this->orders = $orders;
    }

    /**
     * GET /api/orders
     * Lista todos os pedidos do tenant.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        return $this->respond($this->orders->list($tenantId));
    }

    /**
     * GET /api/orders/{id}
     * Exibe detalhes do pedido.
     */
    public function show($id = null)
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $order = $this->orders->get((int) $id, $tenantId);
        if (! $order) {
            return $this->failNotFound('Pedido não encontrado');
        }
        return $this->respond($order);
    }

    /**
     * POST /api/orders
     * Cria um novo pedido.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        $data = $this->request->getJSON(true);
        try {
            $order = $this->orders->create($tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respondCreated($order);
    }

    /**
     * PATCH /api/orders/{id}
     * Atualiza o status de um pedido. Espera JSON {"status": "..."}
     */
    public function update($id = null)
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $data = $this->request->getJSON(true);
        if (! isset($data['status'])) {
            return $this->failValidationError('Campo status obrigatório');
        }
        $order = $this->orders->updateStatus((int) $id, $tenantId, $data['status']);
        if (! $order) {
            return $this->failNotFound('Pedido não encontrado');
        }
        return $this->respond($order);
    }

    /**
     * DELETE /api/orders/{id}
     * Remove um pedido.
     */
    public function delete($id = null)
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $ok = $this->orders->delete((int) $id, $tenantId);
        if (! $ok) {
            return $this->failNotFound('Pedido não encontrado');
        }
        return $this->respondDeleted(['id' => $id]);
    }
}