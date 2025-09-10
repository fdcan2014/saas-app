<?php
namespace Modules\Product\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Product\Services\ProductService;
use Modules\Core\Services\ContextService;
use RuntimeException;

/**
 * Controlador REST para gestão de produtos por tenant.
 */
class ProductController extends ResourceController
{
    protected ProductService $products;

    public function __construct(ProductService $products)
    {
        $this->products = $products;
    }

    /**
     * GET /api/products
     *
     * Lista todos os produtos do tenant atual.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        return $this->respond($this->products->list($tenantId));
    }

    /**
     * GET /api/products/{id}
     *
     * Exibe um produto.
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
        $product = $this->products->get((int) $id, $tenantId);
        if (! $product) {
            return $this->failNotFound('Produto não encontrado');
        }
        return $this->respond($product);
    }

    /**
     * POST /api/products
     *
     * Cria um novo produto.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        $data = $this->request->getJSON(true);
        try {
            $product = $this->products->create($tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respondCreated($product);
    }

    /**
     * PUT/PATCH /api/products/{id}
     *
     * Atualiza um produto.
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
            $product = $this->products->update((int) $id, $tenantId, $data);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        if (! $product) {
            return $this->failNotFound('Produto não encontrado');
        }
        return $this->respond($product);
    }

    /**
     * DELETE /api/products/{id}
     *
     * Remove um produto.
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
        $ok = $this->products->delete((int) $id, $tenantId);
        if (! $ok) {
            return $this->failNotFound('Produto não encontrado');
        }
        return $this->respondDeleted(['id' => $id]);
    }
}