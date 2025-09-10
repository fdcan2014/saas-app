<?php
namespace Modules\Tenant\Repositories;

use Modules\Tenant\Entities\TenantEntity;

/**
 * Repositório para acessar dados de tenants.
 */
class TenantRepository
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
     * Retorna todos os tenants cadastrados.
     *
     * @return TenantEntity[]
     */
    public function findAll(): array
    {
        $rows = $this->db->table('tenants')->get()->getResult();
        return array_map(fn ($row) => new TenantEntity((array) $row), $rows);
    }

    /**
     * Localiza um tenant pelo domínio fornecido.
     */
    public function findByDomain(string $domain): ?TenantEntity
    {
        $row = $this->db->table('tenants')->where('domain', $domain)->get()->getRow();
        return $row ? new TenantEntity((array) $row) : null;
    }

    /**
     * Localiza um tenant pelo ID.
     */
    public function find(int $id): ?TenantEntity
    {
        $row = $this->db->table('tenants')->where('id', $id)->get()->getRow();
        return $row ? new TenantEntity((array) $row) : null;
    }

    /**
     * Cria um novo tenant e retorna a entidade criada.
     */
    public function create(array $data): TenantEntity
    {
        $this->db->table('tenants')->insert($data);
        $id = $this->db->insertID();
        $data['id'] = $id;
        return new TenantEntity($data);
    }

    /**
     * Atualiza um tenant existente e retorna a entidade atualizada.
     */
    public function update(int $id, array $data): ?TenantEntity
    {
        $this->db->table('tenants')->where('id', $id)->update($data);
        return $this->find($id);
    }

    /**
     * Remove um tenant por ID.
     */
    public function delete(int $id): bool
    {
        return $this->db->table('tenants')->delete(['id' => $id]);
    }
}