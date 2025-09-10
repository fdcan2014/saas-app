<?php
namespace Modules\Auth\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar as tabelas básicas de autenticação.
 *
 * Esta migration é apenas ilustrativa — em um projeto real você deve
 * implementar os métodos `up()` e `down()` utilizando o Forge do
 * CodeIgniter para criar as tabelas com todas as colunas necessárias.
 */
class CreateUsersTable extends Migration
{
    public function up()
    {
        // Cria a tabela `users`.  Cada usuário pertence a um tenant.
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
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        // O par tenant_id + email deve ser único
        $this->forge->addUniqueKey(['tenant_id', 'email']);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}