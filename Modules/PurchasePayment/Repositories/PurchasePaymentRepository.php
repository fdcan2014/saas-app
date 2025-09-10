<?php
namespace Modules\PurchasePayment\Repositories;

use Modules\PurchasePayment\Entities\PurchasePaymentEntity;

/**
 * Repositório para persistência de pagamentos de compras.
 */
class PurchasePaymentRepository
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria um pagamento de compra.
     */
    public function create(array $data): PurchasePaymentEntity
    {
        $this->db->table('purchase_payments')->insert($data);
        $data['id'] = $this->db->insertID();
        return new PurchasePaymentEntity($data);
    }

    /**
     * Lista pagamentos por tenant e opcionalmente por purchase_id.
     *
     * @return PurchasePaymentEntity[]
     */
    public function findByTenant(int $tenantId, ?int $purchaseId = null): array
    {
        $builder = $this->db->table('purchase_payments')->where('tenant_id', $tenantId);
        if ($purchaseId) {
            $builder->where('purchase_id', $purchaseId);
        }
        $rows = $builder->orderBy('created_at', 'DESC')->get()->getResult();
        return array_map(fn ($row) => new PurchasePaymentEntity((array) $row), $rows);
    }

    /**
     * Atualiza um pagamento existente.
     */
    public function update(int $id, int $tenantId, array $data): ?PurchasePaymentEntity
    {
        $this->db->table('purchase_payments')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        $row = $this->db->table('purchase_payments')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        return $row ? new PurchasePaymentEntity((array) $row) : null;
    }

    /**
     * Soma o valor de pagamentos de compra com status 'paid'.
     */
    public function sumPaidAmount(int $tenantId, int $purchaseId): float
    {
        $row = $this->db->table('purchase_payments')
            ->selectSum('amount', 'total_paid')
            ->where('tenant_id', $tenantId)
            ->where('purchase_id', $purchaseId)
            ->where('status', 'paid')
            ->get()->getRow();
        return $row && $row->total_paid ? (float) $row->total_paid : 0.0;
    }
}