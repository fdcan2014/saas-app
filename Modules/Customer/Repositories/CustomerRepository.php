<?php
namespace Modules\Customer\Repositories;

use Modules\Customer\Entities\CustomerEntity;

/**
 * Repositório para acesso a clientes.
 */
class CustomerRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Lista todos os clientes de um tenant.
     *
     * @return CustomerEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('customers')->where('tenant_id', $tenantId)->get()->getResult();
        return array_map(fn ($row) => new CustomerEntity((array) $row), $rows);
    }

    /**
     * Encontra um cliente pelo ID e tenant.
     */
    public function find(int $id, int $tenantId): ?CustomerEntity
    {
        $row = $this->db->table('customers')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new CustomerEntity((array) $row) : null;
    }

    /**
     * Cria um novo cliente.
     */
    public function create(array $data): CustomerEntity
    {
        $this->db->table('customers')->insert($data);
        $data['id'] = $this->db->insertID();
        return new CustomerEntity($data);
    }

    /**
     * Atualiza um cliente existente.
     */
    public function update(int $id, int $tenantId, array $data): ?CustomerEntity
    {
        $this->db->table('customers')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        return $this->find($id, $tenantId);
    }

    /**
     * Remove um cliente.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->db->table('customers')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }
}