<?php
namespace Modules\Integrations\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use Modules\Integrations\Services\ApiKeyService;
use Modules\Core\Services\ContextService;
class ApiKeyController extends ResourceController {
  protected $format='json';
  public function __construct(protected ApiKeyService $service, protected ContextService $ctx) {}
  public function index(){ $tenantId=$this->ctx->getTenantId(); return $this->respond($this->service->list($tenantId)); }
  public function create(){ $tenantId=$this->ctx->getTenantId(); $data=$this->request->getJSON(true)?:[]; $res=$this->service->create($tenantId,$data['name']??'',$data['scopes']??[]); return $this->respondCreated($res); }
  public function delete($id=null){ $tenantId=$this->ctx->getTenantId(); $ok=$this->service->revoke($tenantId,(int)$id); return $ok?$this->respondNoContent():$this->failNotFound(); }
}