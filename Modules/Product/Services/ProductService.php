<?php
namespace Modules\Product\Services;

use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Entities\ProductEntity;
use Modules\Category\Repositories\CategoryRepository;
use RuntimeException;

/**
 * Serviço para regras de negócio de produtos.
 */
class ProductService
{
    protected ProductRepository $repo;
    /**
     * Repositório de categorias para validação opcional.
     */
    protected CategoryRepository $categoryRepo;

    public function __construct(ProductRepository $repo, ?CategoryRepository $categoryRepo = null)
    {
        $this->repo = $repo;
        // Instancia um repositório de categorias se não fornecido
        $this->categoryRepo = $categoryRepo ?? new CategoryRepository();
    }

    /**
     * Lista todos os produtos de um tenant.
     */
    public function list(int $tenantId): array
    {
        return $this->repo->findAllByTenant($tenantId);
    }

    /**
     * Cria um novo produto.
     *
     * @param int $tenantId
     * @param array $data Deve conter: name, sku, description, price, stock_quantity
     */
    public function create(int $tenantId, array $data): ProductEntity
    {
        // Verifica campos obrigatórios
        if (empty($data['name']) || empty($data['sku'])) {
            throw new RuntimeException('Nome e SKU são obrigatórios');
        }
        // Verifica SKU único por tenant
        $existing = $this->repo->findAllByTenant($tenantId);
        foreach ($existing as $prod) {
            if ($prod->sku === $data['sku']) {
                throw new RuntimeException('SKU já utilizado neste tenant');
            }
        }
        // Verifica categoria se fornecida
        if (isset($data['category_id']) && $data['category_id']) {
            $category = $this->categoryRepo->find($data['category_id'], $tenantId);
            if (! $category) {
                throw new RuntimeException('Categoria informada não encontrada para este tenant');
            }
        } else {
            $data['category_id'] = null;
        }
        // Prepara dados
        $data['tenant_id']      = $tenantId;
        $data['price']          = isset($data['price']) ? (float) $data['price'] : 0.0;
        $data['stock_quantity'] = isset($data['stock_quantity']) ? (int) $data['stock_quantity'] : 0;
        $data['created_at']     = date('Y-m-d H:i:s');
        return $this->repo->create($data);
    }

    /**
     * Atualiza um produto existente.
     */
    public function update(int $id, int $tenantId, array $data): ?ProductEntity
    {
        // Verifica se o produto existe
        $product = $this->repo->find($id, $tenantId);
        if (! $product) {
            return null;
        }
        // Se o SKU for alterado, verifica duplicidade
        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            $products = $this->repo->findAllByTenant($tenantId);
            foreach ($products as $prod) {
                if ($prod->sku === $data['sku'] && $prod->id !== $id) {
                    throw new RuntimeException('SKU já utilizado neste tenant');
                }
            }
        }
        // Prepara campos de atualização
        $updateData = [];
        foreach (['name', 'sku', 'description'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        if (isset($data['price'])) {
            $updateData['price'] = (float) $data['price'];
        }
        if (isset($data['stock_quantity'])) {
            $updateData['stock_quantity'] = (int) $data['stock_quantity'];
        }
        // Categoria
        if (array_key_exists('category_id', $data)) {
            $categoryId = $data['category_id'];
            if ($categoryId) {
                $category = $this->categoryRepo->find($categoryId, $tenantId);
                if (! $category) {
                    throw new RuntimeException('Categoria informada não encontrada para este tenant');
                }
                $updateData['category_id'] = $categoryId;
            } else {
                $updateData['category_id'] = null;
            }
        }
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        return $this->repo->update($id, $tenantId, $updateData);
    }

    /**
     * Remove um produto.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->repo->delete($id, $tenantId);
    }

    /**
     * Retorna um produto específico do tenant.
     */
    public function get(int $id, int $tenantId): ?ProductEntity
    {
        return $this->repo->find($id, $tenantId);
    }
}