<?php
namespace Modules\Integrations\Repositories;
class ApiKeyRepository {
  protected \CodeIgniter\Database\ConnectionInterface $db;
  public function __construct(){ $this->db = \Config\Database::connect(); }
  public function listByTenant(int $tenantId): array {
    return $this->db->table('api_keys')->where('tenant_id',$tenantId)->get()->getResultArray();
  }
  public function create(array $data): int {
    $this->db->table('api_keys')->insert($data); return (int)$this->db->insertID();
  }
  public function revoke(int $tenantId, int $id): bool {
    return (bool)$this->db->table('api_keys')->where(['id'=>$id,'tenant_id'=>$tenantId])
      ->update(['revoked_at'=>date('c'),'updated_at'=>date('c')]);
  }
}