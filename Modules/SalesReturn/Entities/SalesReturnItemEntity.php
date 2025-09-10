<?php
namespace Modules\SalesReturn\Entities;

/**
 * Entidade de item de devolução de vendas.
 */
class SalesReturnItemEntity
{
    public int $id;
    public int $sales_return_id;
    public int $product_id;
    public int $quantity;
    public float $price;
    public float $discount;
    public float $total;

    public function __construct(array $data = [])
    {
        $this->id             = $data['id'] ?? 0;
        $this->sales_return_id= $data['sales_return_id'] ?? 0;
        $this->product_id     = $data['product_id'] ?? 0;
        $this->quantity       = isset($data['quantity']) ? (int) $data['quantity'] : 0;
        $this->price          = isset($data['price']) ? (float) $data['price'] : 0.0;
        $this->discount       = isset($data['discount']) ? (float) $data['discount'] : 0.0;
        $this->total          = isset($data['total']) ? (float) $data['total'] : 0.0;
    }
}