<?php
namespace Modules\Integrations\Services;
use Modules\Integrations\Repositories\IntegrationCredentialRepository;
use Modules\Core\Services\CryptoService;
class IntegrationCredentialService {
  public function __construct(protected IntegrationCredentialRepository $repo, protected CryptoService $crypto) {}
  public function list(int $tenantId): array { return $this->repo->listByTenant($tenantId); }
  public function upsert(int $tenantId, string $provider, array $credentials, ?array $meta): array {
    $enc = $this->crypto->encrypt(json_encode($credentials, JSON_UNESCAPED_UNICODE));
    $id = $this->repo->upsert($tenantId,$provider,['tenant_id'=>$tenantId,'provider'=>$provider,'credentials_enc'=>$enc,'meta'=>$meta?json_encode($meta):null,'updated_at'=>date('c')]);
    return ['id'=>$id,'provider'=>$provider,'meta'=>$meta ?? new \stdClass(),'hasCredentials'=>true];
  }
  public function delete(int $tenantId, int $id): bool { return $this->repo->delete($tenantId, $id); }
}