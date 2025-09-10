<?php
namespace Modules\Integrations\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use Modules\Integrations\Services\IntegrationCredentialService;
use Modules\Core\Services\ContextService;
class IntegrationCredentialController extends ResourceController {
  protected $format='json';
  public function __construct(protected IntegrationCredentialService $service, protected ContextService $ctx) {}
  public function index(){ $tenantId=$this->ctx->getTenantId(); return $this->respond($this->service->list($tenantId)); }
  public function create(){ $tenantId=$this->ctx->getTenantId(); $data=$this->request->getJSON(true)?:[]; $provider=$data['provider']??''; $credentials=$data['credentials']??[]; $meta=$data['meta']??null; $res=$this->service->upsert($tenantId,$provider,$credentials,$meta); return $this->respond($res); }
  public function delete($id=null){ $tenantId=$this->ctx->getTenantId(); $ok=$this->service->delete($tenantId,(int)$id); return $ok?$this->respondNoContent():$this->failNotFound(); }
}