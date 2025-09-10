<?php
namespace Modules\Auth\Services;

use Modules\Auth\Repositories\UserRepository;

/**
 * Serviço para checagem de permissões e roles multi-tenant.
 */
class AuthorizationService
{
    protected UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Verifica se um usuário tem determinada permissão dentro de um tenant.
     */
    public function can(int $userId, string $permission, int $tenantId): bool
    {
        // Descobrir o profile do usuário
        $profile = $this->users->findProfile($userId, $tenantId);
        if (! $profile) {
            return false;
        }
        $db = \Config\Database::connect();
        // Identifica o ID da permissão para o tenant
        $permRow = $db->table('permissions')
            ->select('id')
            ->where('name', $permission)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        if (! $permRow) {
            return false;
        }
        $permissionId = (int) $permRow->id;
        // Verifica permissão direta ao profile
        $exists = $db->table('permission_profile')
            ->where('permission_id', $permissionId)
            ->where('profile_id', $profile->profile_id)
            ->where('tenant_id', $tenantId)
            ->countAllResults();
        if ($exists > 0) {
            return true;
        }
        // Verifica permissão via roles
        $builder = $db->table('role_profile');
        $builder->select('1');
        $builder->join('permission_role', 'permission_role.role_id = role_profile.role_id AND permission_role.tenant_id = role_profile.tenant_id');
        $builder->where('role_profile.profile_id', $profile->profile_id);
        $builder->where('role_profile.tenant_id', $tenantId);
        $builder->where('permission_role.permission_id', $permissionId);
        $builder->where('permission_role.tenant_id', $tenantId);
        $builder->limit(1);
        $row = $builder->get()->getRow();
        return $row ? true : false;
    }

    /**
     * Recupera a lista de papéis (roles) do usuário para um determinado tenant.
     */
    public function roles(int $userId, int $tenantId): array
    {
        $profile = $this->users->findProfile($userId, $tenantId);
        if (! $profile) {
            return [];
        }
        $db = \Config\Database::connect();
        // Obtém todos os nomes de roles associados ao profile
        $rows = $db->table('role_profile')
            ->select('roles.name')
            ->join('roles', 'roles.id = role_profile.role_id AND roles.tenant_id = role_profile.tenant_id')
            ->where('role_profile.profile_id', $profile->profile_id)
            ->where('role_profile.tenant_id', $tenantId)
            ->get()->getResult();
        $roles = [];
        foreach ($rows as $row) {
            $roles[] = $row->name;
        }
        return $roles;
    }

    /**
     * Define (ou redefine) a lista de papéis para um perfil em um tenant.
     *
     * Remove todas as associações existentes e insere as novas.  Não
     * valida se os role_ids pertencem ao tenant; isso deve ser feito
     * previamente.
     */
    public function assignRoles(int $profileId, array $roleIds, int $tenantId): void
    {
        $db = \Config\Database::connect();
        // Remove todas as associações atuais
        $db->table('role_profile')
            ->where('profile_id', $profileId)
            ->where('tenant_id', $tenantId)
            ->delete();
        // Insere novas associações
        $batch = [];
        foreach ($roleIds as $roleId) {
            $batch[] = [
                'profile_id' => $profileId,
                'role_id'    => $roleId,
                'tenant_id'  => $tenantId,
            ];
        }
        if (! empty($batch)) {
            $db->table('role_profile')->insertBatch($batch);
        }
    }
}