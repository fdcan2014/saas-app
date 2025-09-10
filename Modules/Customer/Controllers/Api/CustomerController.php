<?php
namespace Modules\Customer\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Customer\Services\CustomerService;
use Modules\Core\Services\ContextService;
use RuntimeException;

/**
 * Controlador REST para gerenciamento de clientes por tenant.
 */
class CustomerController extends ResourceController
{
    protected CustomerService $customers;

    public function __construct(CustomerService $customers)
    {
        $this->customers = $customers;
    }

    /**
     * GET /api/customers
     *
     * Lista todos os clientes do tenant atual.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        return $this->respond($this->customers->list($tenantId));
    }

    /**
     * GET /api/customers/{id}
     *
     * Exibe um cliente específico.
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
        $customer = $this->customers->get((int) $id, $tenantId);
        if (! $customer) {
            return $this->failNotFound('Cliente não encontrado');
        }
        return $this->respond($customer);
    }

    /**
     * POST /api/customers
     *
     * Cria um novo cliente.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        $data = $this->request->getJSON(true);
        try {
            $customer = $this->customers->create($tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respondCreated($customer);
    }

    /**
     * PUT/PATCH /api/customers/{id}
     *
     * Atualiza um cliente.
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
        try {
            $customer = $this->customers->update((int) $id, $tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        if (! $customer) {
            return $this->failNotFound('Cliente não encontrado');
        }
        return $this->respond($customer);
    }

    /**
     * DELETE /api/customers/{id}
     *
     * Remove um cliente.
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
        $ok = $this->customers->delete((int) $id, $tenantId);
        if (! $ok) {
            return $this->failNotFound('Cliente não encontrado');
        }
        return $this->respondDeleted(['id' => $id]);
    }
}