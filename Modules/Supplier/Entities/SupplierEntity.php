<?php
namespace Modules\Supplier\Entities;

/**
 * Representa um fornecedor associado a um tenant.
 */
class SupplierEntity
{
    public int $id;
    public int $tenant_id;
    public string $name;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $tax_id = null;
    public ?string $contact = null;
    public ?string $address = null;
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