<?php
namespace Modules\Auth\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run()
    {
        $tenantId = 1; // assume que TenantSeeder criou tenant id=1

        // Inserir roles
        $roles = [
            ['tenant_id' => $tenantId, 'name' => 'admin',   'description' => 'Administrador'],
            ['tenant_id' => $tenantId, 'name' => 'manager', 'description' => 'Gerente'],
            ['tenant_id' => $tenantId, 'name' => 'user',    'description' => 'Usuário'],
        ];
        $this->db->table('roles')->insertBatch($roles);

        // Inserir permissions
        $permissions = [
            ['tenant_id' => $tenantId, 'name' => 'manage_users',   'description' => 'Gerenciar usuários'],
            ['tenant_id' => $tenantId, 'name' => 'manage_products','description' => 'Gerenciar produtos'],
            ['tenant_id' => $tenantId, 'name' => 'view_reports',   'description' => 'Ver relatórios'],
        ];
        $this->db->table('permissions')->insertBatch($permissions);

        // Mapear role_id e permission_id para atribuição
        $rolesTable = $this->db->table('roles');
        $permissionsTable = $this->db->table('permissions');
        $roleAdmin   = $rolesTable->where('name', 'admin')->where('tenant_id', $tenantId)->get()->getRow();
        $roleManager = $rolesTable->where('name', 'manager')->where('tenant_id', $tenantId)->get()->getRow();
        $roleUser    = $rolesTable->where('name', 'user')->where('tenant_id', $tenantId)->get()->getRow();

        $permUsers   = $permissionsTable->where('name', 'manage_users')->where('tenant_id', $tenantId)->get()->getRow();
        $permProducts= $permissionsTable->where('name', 'manage_products')->where('tenant_id', $tenantId)->get()->getRow();
        $permReports = $permissionsTable->where('name', 'view_reports')->where('tenant_id', $tenantId)->get()->getRow();

        // Atribuir permissões aos roles
        $permissionRole = [];
        // admin: todas permissões
        foreach ([$permUsers, $permProducts, $permReports] as $perm) {
            $permissionRole[] = [
                'permission_id' => $perm->id,
                'role_id'       => $roleAdmin->id,
                'tenant_id'     => $tenantId,
            ];
        }
        // manager: manage_products, view_reports
        foreach ([$permProducts, $permReports] as $perm) {
            $permissionRole[] = [
                'permission_id' => $perm->id,
                'role_id'       => $roleManager->id,
                'tenant_id'     => $tenantId,
            ];
        }
        // user: view_reports
        $permissionRole[] = [
            'permission_id' => $permReports->id,
            'role_id'       => $roleUser->id,
            'tenant_id'     => $tenantId,
        ];
        $this->db->table('permission_role')->insertBatch($permissionRole);

        // Criar usuário admin na tabela users do Shield (ou atualizar caso exista)
        $adminEmail = 'admin@loja.com';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $userTable = $this->db->table('users');
        // Verifica se já existe um usuário admin com este e‑mail
        $existing = $userTable->where('email', $adminEmail)->get()->getRow();
        if ($existing) {
            $userId = $existing->id;
        } else {
            $userTable->insert([
                'email'         => $adminEmail,
                'password_hash' => $adminPassword,
                'username'      => $adminEmail,
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            $userId = $this->db->insertID();
        }
        // Criar perfil para o usuário no tenant
        $profilesTable = $this->db->table('profiles');
        $profilesTable->insert([
            'user_id'     => $userId,
            'tenant_id'   => $tenantId,
            'display_name'=> 'Administrador',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $profileId = $this->db->insertID();
        // Atribuir role admin ao perfil
        $this->db->table('role_profile')->insert([
            'profile_id' => $profileId,
            'role_id'    => $roleAdmin->id,
            'tenant_id'  => $tenantId,
        ]);
    }
}