<?php
namespace Modules\Integrations\Repositories;
class IntegrationCredentialRepository {
  protected \CodeIgniter\Database\ConnectionInterface $db;
  public function __construct(){ $this->db = \Config\Database::connect(); }
  public function listByTenant(int $tenantId): array {
    $rows = $this->db->table('integration_credentials')->where('tenant_id',$tenantId)->get()->getResultArray();
    foreach($rows as &$r){ $r['has_credentials'] = !empty($r['credentials_enc']); unset($r['credentials_enc']); }
    return $rows;
  }
  public function upsert(int $tenantId, string $provider, array $data): int {
    $row = $this->db->table('integration_credentials')->where(['tenant_id'=>$tenantId,'provider'=>$provider])->get()->getRowArray();
    if($row){ $this->db->table('integration_credentials')->where('id',$row['id'])->update($data); return (int)$row['id']; }
    $this->db->table('integration_credentials')->insert($data); return (int)$this->db->insertID();
  }
  public function delete(int $tenantId, int $id): bool {
    return (bool)$this->db->table('integration_credentials')->where(['id'=>$id,'tenant_id'=>$tenantId])->delete();
  }
}