<?php
namespace Modules\Integrations\Repositories;
class NotificationTemplateRepository {
  protected \CodeIgniter\Database\ConnectionInterface $db;
  public function __construct(){ $this->db = \Config\Database::connect(); }
  public function listByTenant(int $tenantId): array {
    return $this->db->table('notification_templates')->where('tenant_id',$tenantId)->get()->getResultArray();
  }
  public function create(array $data): int {
    $this->db->table('notification_templates')->insert($data); return (int)$this->db->insertID();
  }
  public function update(int $tenantId, int $id, array $data): bool {
    return (bool)$this->db->table('notification_templates')->where(['id'=>$id,'tenant_id'=>$tenantId])->update($data);
  }
  public function delete(int $tenantId, int $id): bool {
    return (bool)$this->db->table('notification_templates')->where(['id'=>$id,'tenant_id'=>$tenantId])->delete();
  }
}