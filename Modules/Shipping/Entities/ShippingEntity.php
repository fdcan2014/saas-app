<?php
namespace Modules\Shipping\Entities;

/**
 * Entidade que representa um envio (shipping) associado a um pedido.
 */
class ShippingEntity
{
    public int $id;
    public int $tenant_id;
    public int $order_id;
    public string $address;
    public ?string $carrier;
    public ?string $tracking_code;
    public string $status;
    public ?string $shipped_at;
    public ?string $delivered_at;
    public string $created_at;
    public ?string $updated_at;

    public function __construct(array $data = [])
    {
        $this->id           = $data['id'] ?? 0;
        $this->tenant_id    = $data['tenant_id'] ?? 0;
        $this->order_id     = $data['order_id'] ?? 0;
        $this->address      = $data['address'] ?? '';
        $this->carrier      = $data['carrier'] ?? null;
        $this->tracking_code= $data['tracking_code'] ?? null;
        $this->status       = $data['status'] ?? 'pending';
        $this->shipped_at   = $data['shipped_at'] ?? null;
        $this->delivered_at = $data['delivered_at'] ?? null;
        $this->created_at   = $data['created_at'] ?? '';
        $this->updated_at   = $data['updated_at'] ?? null;
    }
}