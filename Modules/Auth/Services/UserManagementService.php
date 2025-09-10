<?php
namespace Modules\Auth\Services;

use Modules\Auth\Repositories\UserRepository;
use Modules\Auth\Services\AuthorizationService;
use Modules\Auth\Entities\UserEntity;
use RuntimeException;

/**
 * Serviço para gerenciamento de usuários dentro de um tenant.  Permite
 * listar, criar, atualizar e remover perfis de usuários e gerenciar
 * seus papéis.
 */
class UserManagementService
{
    protected UserRepository $users;
    protected AuthorizationService $authz;

    public function __construct(UserRepository $users, AuthorizationService $authz)
    {
        $this->users = $users;
        $this->authz = $authz;
    }

    /**
     * Lista todos os perfis (usuários) para um tenant.
     */
    public function list(int $tenantId): array
    {
        return $this->users->listProfiles($tenantId);
    }

    /**
     * Cria um novo usuário e seu perfil, atribuindo papéis informados.
     *
     * @param array $data Deve conter: email, password, display_name, role_ids (array)
     * @param int   $tenantId
     */
    public function create(array $data, int $tenantId): array
    {
        $email       = $data['email'] ?? null;
        $password    = $data['password'] ?? null;
        $displayName = $data['display_name'] ?? null;
        $roleIds     = $data['role_ids'] ?? [];
        if (! $email || ! $password) {
            throw new RuntimeException('E-mail e senha são obrigatórios');
        }
        // Verifica se já existe um usuário com esse e-mail no tenant
        $existing = $this->users->findByEmail($email, $tenantId);
        if ($existing) {
            throw new RuntimeException('Usuário com este e-mail já existe neste tenant');
        }
        // Cria usuário e perfil
        $userEntity = new UserEntity([
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name'          => $displayName,
            'tenant_id'     => $tenantId,
        ]);
        $this->users->save($userEntity);
        // Recupera perfil criado para obter profile_id
        $profile = $this->users->findProfile($userEntity->id, $tenantId);
        if (! $profile) {
            throw new RuntimeException('Falha ao criar perfil de usuário');
        }
        // Atribui papéis
        $this->authz->assignRoles($profile->profile_id, $roleIds, $tenantId);
        // Retorna informações do novo usuário
        return [
            'profile_id' => $profile->profile_id,
            'user_id'    => $profile->id,
            'email'      => $profile->email,
            'name'       => $profile->name,
            'roles'      => $roleIds,
        ];
    }

    /**
     * Atualiza um perfil de usuário existente.
     *
     * @param int   $profileId
     * @param array $data Deve conter opcionalmente: email, password, display_name, role_ids
     * @param int   $tenantId
     */
    public function update(int $profileId, array $data, int $tenantId): array
    {
        $profileData = $this->users->findProfileById($profileId);
        if (! $profileData || $profileData['tenant_id'] !== $tenantId) {
            throw new RuntimeException('Perfil não encontrado para este tenant');
        }
        // Atualiza email ou senha se fornecidos
        $userEntity = new UserEntity([
            'id'            => $profileData['user_id'],
            'email'         => $profileData['email'],
            'password_hash' => $profileData['password_hash'],
            'name'          => $profileData['display_name'],
            'tenant_id'     => $tenantId,
            'profile_id'    => $profileId,
        ]);
        $needsSave = false;
        if (! empty($data['email']) && $data['email'] !== $userEntity->email) {
            $userEntity->email = $data['email'];
            $needsSave = true;
        }
        if (! empty($data['password'])) {
            $userEntity->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $needsSave = true;
        }
        if (! empty($data['display_name']) && $data['display_name'] !== $userEntity->name) {
            $userEntity->name = $data['display_name'];
            $needsSave = true;
        }
        if ($needsSave) {
            $this->users->save($userEntity);
        }
        // Atualiza papéis se fornecidos
        if (isset($data['role_ids']) && is_array($data['role_ids'])) {
            $this->authz->assignRoles($profileId, $data['role_ids'], $tenantId);
        }
        return [
            'profile_id' => $profileId,
            'user_id'    => $userEntity->id,
            'email'      => $userEntity->email,
            'name'       => $userEntity->name,
            'roles'      => $this->authz->roles($userEntity->id, $tenantId),
        ];
    }

    /**
     * Remove um perfil e suas associações de papéis.  Se não houver mais
     * perfis para o usuário em qualquer tenant, poderia opcionalmente
     * remover o usuário, mas aqui removemos apenas o perfil.
     */
    public function delete(int $profileId, int $tenantId): bool
    {
        $profileData = $this->users->findProfileById($profileId);
        if (! $profileData || $profileData['tenant_id'] !== $tenantId) {
            throw new RuntimeException('Perfil não encontrado para este tenant');
        }
        $db = \Config\Database::connect();
        // Remove associações de papéis
        $db->table('role_profile')
            ->where('profile_id', $profileId)
            ->where('tenant_id', $tenantId)
            ->delete();
        // Remove permissões diretas
        $db->table('permission_profile')
            ->where('profile_id', $profileId)
            ->where('tenant_id', $tenantId)
            ->delete();
        // Remove o perfil
        return $db->table('profiles')->delete(['id' => $profileId]);
    }
}