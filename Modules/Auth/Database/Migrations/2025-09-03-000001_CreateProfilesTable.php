<?php
namespace Modules\Auth\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cria a tabela `profiles`, que faz a ponte entre usuários do Shield e
 * tenants.  Cada usuário pode possuir um perfil para cada loja (tenant)
 * com dados específicos.
 */
class CreateProfilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'tenant_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'tenant_id'], true);
        // Chave estrangeira para a tabela `users` criada pelo Shield
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('profiles');
    }

    public function down()
    {
        $this->forge->dropTable('profiles');
    }
}