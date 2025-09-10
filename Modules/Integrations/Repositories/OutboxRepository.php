<?php
namespace Modules\Integrations\Repositories;
class OutboxRepository {
  protected \CodeIgniter\Database\ConnectionInterface $db;
  public function __construct(){ $this->db = \Config\Database::connect(); }
  public function listByTenant(int $tenantId): array {
    return $this->db->table('outbox_messages')->where('tenant_id',$tenantId)->orderBy('id','DESC')->get()->getResultArray();
  }
  public function create(array $data): int {
    $this->db->table('outbox_messages')->insert($data); return (int)$this->db->insertID();
  }
}