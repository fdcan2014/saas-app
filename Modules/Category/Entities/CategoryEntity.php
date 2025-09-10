<?php
namespace Modules\Category\Entities;

/**
 * Representa uma categoria de produtos.
 */
class CategoryEntity
{
    public int $id;
    public int $tenant_id;
    public string $name;
    public string $slug;
    public ?int $parent_id = null;
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