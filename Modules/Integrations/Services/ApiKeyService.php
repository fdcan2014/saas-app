<?php
namespace Modules\Integrations\Services;
use Modules\Integrations\Repositories\ApiKeyRepository; use RuntimeException;
class ApiKeyService {
  public function __construct(protected ApiKeyRepository $repo) {}
  public function list(int $tenantId): array { return $this->repo->listByTenant($tenantId); }
  public function create(int $tenantId, string $name, array $scopes): array {
    if(!$name) throw new RuntimeException('name required');
    $tokenId = bin2hex(random_bytes(6));
    $random = bin2hex(random_bytes(24));
    $plaintext = "v1.api_{$tokenId}_{$random}";
    $tokenHash = hash('sha256', $plaintext);
    $last4 = substr($random, -4);
    $id = $this->repo->create([
      'tenant_id'=>$tenantId, 'name'=>$name, 'token_id'=>$tokenId, 'token_hash'=>$tokenHash,
      'last4'=>$last4, 'scopes'=>json_encode($scopes), 'created_at'=>date('c'),
    ]);
    return ['key'=>['id'=>$id,'name'=>$name,'tokenId'=>$tokenId,'last4'=>$last4,'scopes'=>$scopes,'createdAt'=>date('c')],'plaintextToken'=>$plaintext];
  }
  public function revoke(int $tenantId, int $id): bool { return $this->repo->revoke($tenantId,$id); }
}