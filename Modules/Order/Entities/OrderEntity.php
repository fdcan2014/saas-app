<?php
namespace Modules\Order\Entities;

/**
 * Representa um pedido de venda.
 */
class OrderEntity
{
    public int $id;
    public int $tenant_id;
    public ?int $customer_id = null;
    public string $status;
    public float $total;

    /**
     * ID do cupom aplicado ao pedido, se houver.
     */
    public ?int $coupon_id = null;

    /**
     * Valor total de desconto aplicado ao pedido.
     */
    public float $discount_total = 0.0;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    /**
     * @var OrderItemEntity[]
     */
    public array $items = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}