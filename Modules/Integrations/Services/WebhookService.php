<?php
namespace Modules\Integrations\Services;
use Modules\Integrations\Repositories\WebhookRepository;
use Modules\Integrations\Repositories\OutboxRepository;
use Modules\Core\Services\CryptoService;
class WebhookService {
  public function __construct(protected WebhookRepository $repo, protected OutboxRepository $outbox, protected CryptoService $crypto) {}
  public function list(int $tenantId): array { $rows=$this->repo->listByTenant($tenantId); foreach($rows as &$r){unset($r['secret_enc']);} return $rows; }
  public function create(int $tenantId, array $data): array {
    $secretEnc = (isset($data['secret']) && $data['secret']!=='') ? $this->crypto->encrypt($data['secret']) : null;
    $payload = ['tenant_id'=>$tenantId,'event'=>$data['event']??'','url'=>$data['url']??'','secret_enc'=>$secretEnc,'active'=>isset($data['active'])?(int)!!$data['active']:1,'created_at'=>date('c')];
    $id = $this->repo->create($payload); return ['id'=>$id]+$payload;
  }
  public function update(int $tenantId, int $id, array $data): bool {
    $upd=['updated_at'=>date('c')]; foreach(['event','url','active'] as $k) if(isset($data[$k])) $upd[$k]=$data[$k];
    if(array_key_exists('secret',$data)){ $upd['secret_enc']=($data['secret']!==null&&$data['secret']!=='') ? $this->crypto->encrypt($data['secret']) : null; }
    return $this->repo->update($tenantId,$id,$upd);
  }
  public function delete(int $tenantId, int $id): bool { return $this->repo->delete($tenantId,$id); }
  public function enqueueEvent(int $tenantId, string $event, array $payload): int {
    return $this->outbox->create(['tenant_id'=>$tenantId,'event'=>$event,'payload_json'=>json_encode($payload, JSON_UNESCAPED_UNICODE),'status'=>'pending','attempts'=>0,'created_at'=>date('c')]);
  }
}