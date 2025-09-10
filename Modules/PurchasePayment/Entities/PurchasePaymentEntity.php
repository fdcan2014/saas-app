<?php
namespace Modules\PurchasePayment\Entities;

/**
 * Entidade que representa um pagamento de compra.
 */
class PurchasePaymentEntity
{
    public int $id;
    public int $tenant_id;
    public int $purchase_id;
    public float $amount;
    public ?string $method;
    public string $status;
    public ?string $paid_at;
    public string $created_at;

    public function __construct(array $data = [])
    {
        $this->id         = $data['id'] ?? 0;
        $this->tenant_id  = $data['tenant_id'] ?? 0;
        $this->purchase_id= $data['purchase_id'] ?? 0;
        $this->amount     = isset($data['amount']) ? (float) $data['amount'] : 0.0;
        $this->method     = $data['method'] ?? null;
        $this->status     = $data['status'] ?? 'pending';
        $this->paid_at    = $data['paid_at'] ?? null;
        $this->created_at = $data['created_at'] ?? '';
    }
}