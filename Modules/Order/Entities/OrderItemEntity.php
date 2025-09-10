<?php
namespace Modules\Order\Entities;

/**
 * Representa um item de pedido.
 */
class OrderItemEntity
{
    public int $id;
    public int $order_id;
    public int $product_id;
    public int $quantity;
    public float $price;
    public float $discount;
    public float $total;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}