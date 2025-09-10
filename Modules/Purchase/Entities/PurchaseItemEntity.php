<?php
namespace Modules\Purchase\Entities;

/**
 * Entidade que representa um item de compra.
 */
class PurchaseItemEntity
{
    public int $id;
    public int $purchase_id;
    public int $product_id;
    public int $quantity;
    public float $price;
    public float $discount;
    public float $total;

    public function __construct(array $data = [])
    {
        $this->id          = $data['id'] ?? 0;
        $this->purchase_id = $data['purchase_id'] ?? 0;
        $this->product_id  = $data['product_id'] ?? 0;
        $this->quantity    = isset($data['quantity']) ? (int) $data['quantity'] : 0;
        $this->price       = isset($data['price']) ? (float) $data['price'] : 0.0;
        $this->discount    = isset($data['discount']) ? (float) $data['discount'] : 0.0;
        $this->total       = isset($data['total']) ? (float) $data['total'] : 0.0;
    }
}