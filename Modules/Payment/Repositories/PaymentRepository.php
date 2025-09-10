<?php
namespace Modules\Payment\Repositories;

use Modules\Payment\Entities\PaymentEntity;

/**
 * Repositório para operações de persistência de pagamentos.
 */
class PaymentRepository
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria um pagamento.
     */
    public function create(array $data): PaymentEntity
    {
        $this->db->table('payments')->insert($data);
        $data['id'] = $this->db->insertID();
        return new PaymentEntity($data);
    }

    /**
     * Lista pagamentos de um tenant, opcionalmente filtrando por order_id.
     *
     * @param int      $tenantId
     * @param int|null $orderId
     * @return PaymentEntity[]
     */
    public function findByTenant(int $tenantId, ?int $orderId = null): array
    {
        $builder = $this->db->table('payments')->where('tenant_id', $tenantId);
        if ($orderId) {
            $builder->where('order_id', $orderId);
        }
        $rows = $builder->orderBy('created_at', 'DESC')->get()->getResult();
        return array_map(fn ($row) => new PaymentEntity((array) $row), $rows);
    }

    /**
     * Soma o valor de pagamentos com status 'paid' para um pedido.
     *
     * @param int $tenantId
     * @param int $orderId
     * @return float
     */
    public function sumPaidAmount(int $tenantId, int $orderId): float
    {
        $row = $this->db->table('payments')
            ->selectSum('amount', 'total_paid')
            ->where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->where('status', 'paid')
            ->get()->getRow();
        return $row && $row->total_paid ? (float) $row->total_paid : 0.0;
    }

    /**
     * Atualiza um pagamento.
     */
    public function update(int $id, int $tenantId, array $data): ?PaymentEntity
    {
        $this->db->table('payments')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        $row = $this->db->table('payments')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new PaymentEntity((array) $row) : null;
    }
}