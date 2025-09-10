<?php
namespace Modules\Shipping\Repositories;

use Modules\Shipping\Entities\ShippingEntity;

/**
 * Repositório para persistência de envios.
 */
class ShippingRepository
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria um registro de envio.
     */
    public function create(array $data): ShippingEntity
    {
        $this->db->table('shippings')->insert($data);
        $data['id'] = $this->db->insertID();
        return new ShippingEntity($data);
    }

    /**
     * Atualiza um envio existente.
     */
    public function update(int $id, int $tenantId, array $data): ?ShippingEntity
    {
        $this->db->table('shippings')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        $row = $this->db->table('shippings')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new ShippingEntity((array) $row) : null;
    }

    /**
     * Recupera um envio.
     */
    public function find(int $id, int $tenantId): ?ShippingEntity
    {
        $row = $this->db->table('shippings')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new ShippingEntity((array) $row) : null;
    }

    /**
     * Lista envios do tenant, opcionalmente filtrando por order_id.
     *
     * @param int      $tenantId
     * @param int|null $orderId
     * @return ShippingEntity[]
     */
    public function findByTenant(int $tenantId, ?int $orderId = null): array
    {
        $builder = $this->db->table('shippings')->where('tenant_id', $tenantId);
        if ($orderId) {
            $builder->where('order_id', $orderId);
        }
        $rows = $builder->orderBy('created_at', 'DESC')->get()->getResult();
        return array_map(fn ($row) => new ShippingEntity((array) $row), $rows);
    }
}