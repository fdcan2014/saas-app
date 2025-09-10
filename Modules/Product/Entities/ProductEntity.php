<?php
namespace Modules\Product\Entities;

/**
 * Representa um produto no sistema.
 */
class ProductEntity
{
    public int $id;
    public int $tenant_id;
    public string $name;
    public string $sku;
    public ?string $description = null;
    public float $price;
    public int $stock_quantity;
    /**
     * ID da categoria do produto (opcional).
     */
    public ?int $category_id = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}