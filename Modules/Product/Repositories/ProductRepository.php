<?php
namespace Modules\Product\Repositories;

use Modules\Product\Entities\ProductEntity;

/**
 * Repositório para acesso a produtos.
 */
class ProductRepository
{
    /**
     * @var \CodeIgniter\Database\ConnectionInterface
     */
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Retorna todos os produtos de um tenant.
     *
     * @return ProductEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('products')->where('tenant_id', $tenantId)->get()->getResult();
        return array_map(fn ($row) => new ProductEntity((array) $row), $rows);
    }

    /**
     * Localiza um produto pelo ID e tenant.
     */
    public function find(int $id, int $tenantId): ?ProductEntity
    {
        $row = $this->db->table('products')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new ProductEntity((array) $row) : null;
    }

    /**
     * Cria um novo produto.
     */
    public function create(array $data): ProductEntity
    {
        $this->db->table('products')->insert($data);
        $id = $this->db->insertID();
        $data['id'] = $id;
        return new ProductEntity($data);
    }

    /**
     * Atualiza um produto existente.
     */
    public function update(int $id, int $tenantId, array $data): ?ProductEntity
    {
        $this->db->table('products')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        return $this->find($id, $tenantId);
    }

    /**
     * Remove um produto.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->db->table('products')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }
}