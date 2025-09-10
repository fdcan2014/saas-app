<?php
namespace Modules\Tenant\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Tenant\Services\TenantService;
use RuntimeException;

/**
 * Controlador REST para gestão de lojas (tenants).
 */
class TenantController extends ResourceController
{
    protected TenantService $tenants;

    public function __construct(TenantService $tenants)
    {
        $this->tenants = $tenants;
    }

    /**
     * GET /api/tenants
     *
     * Lista todos os tenants.
     */
    public function index()
    {
        return $this->respond($this->tenants->all());
    }

    /**
     * GET /api/tenants/{id}
     *
     * Exibe os detalhes de um tenant.
     */
    public function show($id = null)
    {
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $tenant = $this->tenants->get((int) $id);
        if (! $tenant) {
            return $this->failNotFound('Tenant não encontrado');
        }
        return $this->respond($tenant);
    }

    /**
     * POST /api/tenants
     *
     * Cria um novo tenant.
     */
    public function create()
    {
        $data = $this->request->getJSON(true);
        try {
            $tenant = $this->tenants->create($data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respondCreated($tenant);
    }

    /**
     * PUT/PATCH /api/tenants/{id}
     *
     * Atualiza um tenant existente.
     */
    public function update($id = null)
    {
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $data = $this->request->getJSON(true);
        try {
            $tenant = $this->tenants->update((int) $id, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        if (! $tenant) {
            return $this->failNotFound('Tenant não encontrado');
        }
        return $this->respond($tenant);
    }

    /**
     * DELETE /api/tenants/{id}
     *
     * Remove um tenant.
     */
    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $ok = $this->tenants->delete((int) $id);
        if (! $ok) {
            return $this->failNotFound('Tenant não encontrado');
        }
        return $this->respondDeleted(['id' => $id]);
    }
}