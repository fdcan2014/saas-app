<?php
namespace Modules\Integrations\Services;

class ApiKeyAuthService
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function findByPlaintext(?string $bearer): ?array
    {
        if (!$bearer || !str_starts_with($bearer, 'v1.api_')) return null;
        $hash = hash('sha256', $bearer);
        $row = $this->db->table('api_keys')
            ->where('token_hash', $hash)
            ->where('revoked_at', null)
            ->get()->getRowArray();
        if (!$row) return null;
        // Normalize output
        return [
            'id' => (int)$row['id'],
            'tenant_id' => (int)$row['tenant_id'],
            'name' => $row['name'],
            'token_id' => $row['token_id'],
            'last4' => $row['last4'],
            'scopes' => $row['scopes'] ? json_decode($row['scopes'], true) : [],
            'created_at' => $row['created_at'] ?? null,
        ];
    }
}
