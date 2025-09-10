<?php
namespace Modules\Integrations\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use Modules\Integrations\Services\OutboxService;
use Modules\Core\Services\ContextService;
class OutboxController extends ResourceController {
  protected $format='json';
  public function __construct(protected OutboxService $service, protected ContextService $ctx) {}
  public function index(){ $tenantId=$this->ctx->getTenantId(); return $this->respond($this->service->list($tenantId)); }
}