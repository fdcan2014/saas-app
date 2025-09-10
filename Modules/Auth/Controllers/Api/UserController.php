<?php
namespace Modules\Auth\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Auth\Services\UserManagementService;
use Modules\Core\Services\ContextService;
use RuntimeException;

/**
 * Controlador REST para gestão de usuários/profiles dentro de um tenant.
 */
class UserController extends ResourceController
{
    protected UserManagementService $users;

    public function __construct(UserManagementService $users)
    {
        $this->users = $users;
    }

    /**
     * GET /api/users
     *
     * Lista todos os usuários (perfis) do tenant atual.
     */
    public function index()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        return $this->respond($this->users->list($tenantId));
    }

    /**
     * GET /api/users/{id}
     *
     * Exibe um perfil específico.
     */
    public function show($id = null)
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $profileData = $this->users->list($tenantId);
        foreach ($profileData as $profile) {
            if ($profile['profile_id'] == $id) {
                return $this->respond($profile);
            }
        }
        return $this->failNotFound('Usuário não encontrado');
    }

    /**
     * POST /api/users
     *
     * Cria um novo usuário para o tenant atual.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        $data = $this->request->getJSON(true);
        try {
            $result = $this->users->create($data, $tenantId);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respondCreated($result);
    }

    /**
     * PUT/PATCH /api/users/{id}
     *
     * Atualiza um usuário existente.
     */
    public function update($id = null)
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        $data = $this->request->getJSON(true);
        try {
            $result = $this->users->update((int) $id, $data, $tenantId);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        return $this->respond($result);
    }

    /**
     * DELETE /api/users/{id}
     *
     * Remove um usuário (perfil) do tenant atual.
     */
    public function delete($id = null)
    {
        $tenantId = ContextService::getTenantId();
        if (! $tenantId) {
            return $this->fail('Tenant não resolvido', 400);
        }
        if ($id === null) {
            return $this->failValidationError('ID não informado');
        }
        try {
            $ok = $this->users->delete((int) $id, $tenantId);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 409);
        }
        if (! $ok) {
            return $this->failNotFound('Usuário não encontrado');
        }
        return $this->respondDeleted(['profile_id' => $id]);
    }
}