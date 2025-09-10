<?php
namespace Modules\Supplier\Repositories;

use Modules\Supplier\Entities\SupplierEntity;

/**
 * Repositório para acesso a fornecedores.
 */
class SupplierRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Lista todos os fornecedores de um tenant.
     *
     * @return SupplierEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('suppliers')->where('tenant_id', $tenantId)->get()->getResult();
        return array_map(fn ($row) => new SupplierEntity((array) $row), $rows);
    }

    /**
     * Encontra um fornecedor por ID e tenant.
     */
    public function find(int $id, int $tenantId): ?SupplierEntity
    {
        $row = $this->db->table('suppliers')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new SupplierEntity((array) $row) : null;
    }

    /**
     * Cria um novo fornecedor.
     */
    public function create(array $data): SupplierEntity
    {
        $this->db->table('suppliers')->insert($data);
        $data['id'] = $this->db->insertID();
        return new SupplierEntity($data);
    }

    /**
     * Atualiza um fornecedor existente.
     */
    public function update(int $id, int $tenantId, array $data): ?SupplierEntity
    {
        $this->db->table('suppliers')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        return $this->find($id, $tenantId);
    }

    /**
     * Remove um fornecedor.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->db->table('suppliers')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }
}