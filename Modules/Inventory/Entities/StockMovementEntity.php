<?php
namespace Modules\Inventory\Entities;

/**
 * Entidade que representa uma movimentação de estoque.
 */
class StockMovementEntity
{
    public int $id;
    public int $tenant_id;
    public int $product_id;
    public string $type;
    public int $quantity;
    public ?string $reference_type;
    public ?int $reference_id;
    public ?string $description;
    public string $created_at;

    public function __construct(array $data = [])
    {
        $this->id            = $data['id'] ?? 0;
        $this->tenant_id     = $data['tenant_id'] ?? 0;
        $this->product_id    = $data['product_id'] ?? 0;
        $this->type          = $data['type'] ?? '';
        $this->quantity      = isset($data['quantity']) ? (int) $data['quantity'] : 0;
        $this->reference_type = $data['reference_type'] ?? null;
        $this->reference_id   = isset($data['reference_id']) ? (int) $data['reference_id'] : null;
        $this->description    = $data['description'] ?? null;
        $this->created_at    = $data['created_at'] ?? '';
    }
}