<?php
namespace Modules\Category\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Category\Services\CategoryService;
use Modules\Core\Services\ContextService;
use RuntimeException;

/**
 * Controlador REST para gestão de categorias por tenant.
 */
class CategoryController extends ResourceController
{
    protected CategoryService $categories;

    public function __construct(CategoryService $categories)
    {
        $this->categories = $categories;
    }

    /**
     * GET /api/categories
     *
     * Lista todas as categorias do tenant atual.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        return $this->respond($this->categories->list($tenantId));
    }

    /**
     * GET /api/categories/{id}
     *
     * Exibe uma categoria.
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
        $category = $this->categories->get((int) $id, $tenantId);
        if (! $category) {
            return $this->failNotFound('Categoria não encontrada');
        }
        return $this->respond($category);
    }

    /**
     * POST /api/categories
     *
     * Cria uma nova categoria.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        $data = $this->request->getJSON(true);
        try {
            $category = $this->categories->create($tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respondCreated($category);
    }

    /**
     * PUT/PATCH /api/categories/{id}
     *
     * Atualiza uma categoria.
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
            $category = $this->categories->update((int) $id, $tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        if (! $category) {
            return $this->failNotFound('Categoria não encontrada');
        }
        return $this->respond($category);
    }

    /**
     * DELETE /api/categories/{id}
     *
     * Remove uma categoria.
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
        $ok = $this->categories->delete((int) $id, $tenantId);
        if (! $ok) {
            return $this->failNotFound('Categoria não encontrada');
        }
        return $this->respondDeleted(['id' => $id]);
    }
}