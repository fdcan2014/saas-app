<?php
namespace Modules\Integrations\Services;
use Modules\Integrations\Repositories\NotificationTemplateRepository;
class NotificationTemplateService {
  public function __construct(protected NotificationTemplateRepository $repo) {}
  public function list(int $tenantId): array { return $this->repo->listByTenant($tenantId); }
  public function create(int $tenantId, array $data): array {
    $payload=['tenant_id'=>$tenantId,'channel'=>$data['channel']??'email','name'=>$data['name']??'','subject'=>$data['subject']??null,'body'=>$data['body']??'','locale'=>$data['locale']??'pt-BR','created_at'=>date('c')];
    $id=$this->repo->create($payload); return ['id'=>$id]+$payload;
  }
  public function update(int $tenantId, int $id, array $data): bool { return $this->repo->update($tenantId,$id,$data+['updated_at'=>date('c')]); }
  public function delete(int $tenantId, int $id): bool { return $this->repo->delete($tenantId,$id); }
}