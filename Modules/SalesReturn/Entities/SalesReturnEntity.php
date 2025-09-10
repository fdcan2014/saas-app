<?php
namespace Modules\SalesReturn\Entities;

use Modules\SalesReturn\Entities\SalesReturnItemEntity;

/**
 * Entidade de devolução de vendas.
 */
class SalesReturnEntity
{
    public int $id;
    public int $tenant_id;
    public int $order_id;
    public string $status;
    public float $total;
    public string $created_at;
    public ?string $updated_at;
    /**
     * @var SalesReturnItemEntity[]
     */
    public array $items = [];

    public function __construct(array $data = [])
    {
        $this->id        = $data['id'] ?? 0;
        $this->tenant_id = $data['tenant_id'] ?? 0;
        $this->order_id  = $data['order_id'] ?? 0;
        $this->status    = $data['status'] ?? 'pending';
        $this->total     = isset($data['total']) ? (float) $data['total'] : 0.0;
        $this->created_at= $data['created_at'] ?? '';
        $this->updated_at= $data['updated_at'] ?? null;
        if (isset($data['items'])) {
            $this->items = $data['items'];
        }
    }
}