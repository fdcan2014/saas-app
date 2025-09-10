<?php
namespace Modules\Supplier\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Supplier\Services\SupplierService;
use Modules\Core\Services\ContextService;
use RuntimeException;

/**
 * Controlador REST para gerenciamento de fornecedores por tenant.
 */
class SupplierController extends ResourceController
{
    protected SupplierService $suppliers;

    public function __construct(SupplierService $suppliers)
    {
        $this->suppliers = $suppliers;
    }

    /**
     * GET /api/suppliers
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        return $this->respond($this->suppliers->list($tenantId));
    }

    /**
     * GET /api/suppliers/{id}
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
        $supplier = $this->suppliers->get((int) $id, $tenantId);
        if (! $supplier) {
            return $this->failNotFound('Fornecedor não encontrado');
        }
        return $this->respond($supplier);
    }

    /**
     * POST /api/suppliers
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        $data = $this->request->getJSON(true);
        try {
            $supplier = $this->suppliers->create($tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respondCreated($supplier);
    }

    /**
     * PUT/PATCH /api/suppliers/{id}
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
            $supplier = $this->suppliers->update((int) $id, $tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        if (! $supplier) {
            return $this->failNotFound('Fornecedor não encontrado');
        }
        return $this->respond($supplier);
    }

    /**
     * DELETE /api/suppliers/{id}
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
        $ok = $this->suppliers->delete((int) $id, $tenantId);
        if (! $ok) {
            return $this->failNotFound('Fornecedor não encontrado');
        }
        return $this->respondDeleted(['id' => $id]);
    }
}