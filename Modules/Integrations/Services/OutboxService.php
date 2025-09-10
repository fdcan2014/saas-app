<?php
namespace Modules\Integrations\Services;
use Modules\Integrations\Repositories\OutboxRepository;
class OutboxService {
  public function __construct(protected OutboxRepository $repo) {}
  public function list(int $tenantId): array { return $this->repo->listByTenant($tenantId); }
}