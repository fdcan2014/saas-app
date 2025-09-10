<?php
namespace Modules\Tenant\Entities;

/**
 * Representa uma loja (tenant) no sistema.
 */
class TenantEntity
{
    public int $id;
    public string $name;
    public string $domain;
    public string $plan;
    public string $status;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}