<?php
namespace Modules\PurchaseReturn\Entities;

use Modules\PurchaseReturn\Entities\PurchaseReturnItemEntity;

/**
 * Entidade de devolução de compras.
 */
class PurchaseReturnEntity
{
    public int $id;
    public int $tenant_id;
    public int $purchase_id;
    public string $status;
    public float $total;
    public string $created_at;
    public ?string $updated_at;
    /**
     * @var PurchaseReturnItemEntity[]
     */
    public array $items = [];

    public function __construct(array $data = [])
    {
        $this->id          = $data['id'] ?? 0;
        $this->tenant_id   = $data['tenant_id'] ?? 0;
        $this->purchase_id = $data['purchase_id'] ?? 0;
        $this->status      = $data['status'] ?? 'pending';
        $this->total       = isset($data['total']) ? (float) $data['total'] : 0.0;
        $this->created_at  = $data['created_at'] ?? '';
        $this->updated_at  = $data['updated_at'] ?? null;
        if (isset($data['items'])) {
            $this->items = $data['items'];
        }
    }
}