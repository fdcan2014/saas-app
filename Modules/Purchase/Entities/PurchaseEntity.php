<?php
namespace Modules\Purchase\Entities;

use Modules\Purchase\Entities\PurchaseItemEntity;

/**
 * Entidade que representa uma compra.
 */
class PurchaseEntity
{
    public int $id;
    public int $tenant_id;
    public int $supplier_id;
    public string $status;
    public float $total;
    public string $created_at;
    public ?string $updated_at;
    /**
     * @var PurchaseItemEntity[]
     */
    public array $items = [];

    public function __construct(array $data = [])
    {
        $this->id         = $data['id'] ?? 0;
        $this->tenant_id  = $data['tenant_id'] ?? 0;
        $this->supplier_id = $data['supplier_id'] ?? 0;
        $this->status     = $data['status'] ?? 'pending';
        $this->total      = isset($data['total']) ? (float) $data['total'] : 0.0;
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? null;
        if (isset($data['items'])) {
            $this->items = $data['items'];
        }
    }
}