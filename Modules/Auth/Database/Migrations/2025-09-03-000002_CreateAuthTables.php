<?php
namespace Modules\Auth\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar tabelas de roles, permissões e relações, bem como
 * tabelas auxiliares de autenticação (auth_identities, auth_tokens,
 * login_attempts, audit_logs).  Todas as tabelas relacionadas a usuários
 * e permissões incluem `tenant_id` para suportar multi‑tenant.
 */
class CreateAuthTables extends Migration
{
    public function up()
    {
        // Tabela de roles (papéis)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['tenant_id', 'name']);
        $this->forge->createTable('roles');

        // Tabela de permissions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['tenant_id', 'name']);
        $this->forge->createTable('permissions');

        // Pivot: role_profile (papéis por perfil)
        $this->forge->addField([
            'profile_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'role_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'tenant_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
        ]);
        $this->forge->addKey(['profile_id', 'role_id', 'tenant_id'], true);
        $this->forge->addForeignKey('profile_id', 'profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_profile');

        // Pivot: permission_role (permissões por papel)
        $this->forge->addField([
            'permission_id' => ['type' => 'INT', 'unsigned' => true],
            'role_id'       => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'     => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['permission_id', 'role_id', 'tenant_id'], true);
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permission_role');

        // Pivot: permission_profile (permissões diretas ao perfil)
        $this->forge->addField([
            'permission_id' => ['type' => 'INT', 'unsigned' => true],
            'profile_id'    => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'     => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['permission_id', 'profile_id', 'tenant_id'], true);
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('profile_id', 'profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permission_profile');

        /*
         * As tabelas de identidades, tokens, tentativas de login e auditoria
         * são providas pelo CodeIgniter Shield e não devem ser criadas aqui.
         * Apenas definimos as nossas tabelas de controle de permissões e
         * papéis específicas para o escopo multi‑tenant.  As tabelas do
         * Shield permanecem inalteradas e serão criadas pelas migrations do
         * pacote quando executadas no projeto real.
         */
    }

    public function down()
    {
        // A ordem de exclusão deve ser inversa à criação para respeitar
        // dependências de chaves estrangeiras.
        $this->forge->dropTable('permission_profile');
        $this->forge->dropTable('permission_role');
        $this->forge->dropTable('role_profile');
        // Utilizamos o parâmetro "true" para evitar exceções caso as tabelas
        // já tenham sido removidas ou não existam.
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
    }
}