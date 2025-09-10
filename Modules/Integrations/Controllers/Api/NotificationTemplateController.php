<?php
namespace Modules\Integrations\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use Modules\Integrations\Services\NotificationTemplateService;
use Modules\Core\Services\ContextService;
class NotificationTemplateController extends ResourceController {
  protected $format='json';
  public function __construct(protected NotificationTemplateService $service, protected ContextService $ctx) {}
  public function index(){ $tenantId=$this->ctx->getTenantId(); return $this->respond($this->service->list($tenantId)); }
  public function create(){ $tenantId=$this->ctx->getTenantId(); $data=$this->request->getJSON(true)?:[]; $res=$this->service->create($tenantId,$data); return $this->respondCreated($res); }
  public function update($id=null){ $tenantId=$this->ctx->getTenantId(); $data=$this->request->getJSON(true)?:[]; $ok=$this->service->update($tenantId,(int)$id,$data); return $ok?$this->respond($data):$this->failNotFound(); }
  public function delete($id=null){ $tenantId=$this->ctx->getTenantId(); $ok=$this->service->delete($tenantId,(int)$id); return $ok?$this->respondNoContent():$this->failNotFound(); }
}