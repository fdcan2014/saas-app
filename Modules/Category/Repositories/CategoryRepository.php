<?php
namespace Modules\Category\Repositories;

use Modules\Category\Entities\CategoryEntity;

class CategoryRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Lista todas as categorias de um tenant.
     *
     * @return CategoryEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('categories')->where('tenant_id', $tenantId)->get()->getResult();
        return array_map(fn ($row) => new CategoryEntity((array) $row), $rows);
    }

    /**
     * Encontra uma categoria por ID.
     */
    public function find(int $id, int $tenantId): ?CategoryEntity
    {
        $row = $this->db->table('categories')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new CategoryEntity((array) $row) : null;
    }

    /**
     * Cria uma nova categoria.
     */
    public function create(array $data): CategoryEntity
    {
        $this->db->table('categories')->insert($data);
        $data['id'] = $this->db->insertID();
        return new CategoryEntity($data);
    }

    /**
     * Atualiza uma categoria existente.
     */
    public function update(int $id, int $tenantId, array $data): ?CategoryEntity
    {
        $this->db->table('categories')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        return $this->find($id, $tenantId);
    }

    /**
     * Remove uma categoria.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->db->table('categories')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }
}