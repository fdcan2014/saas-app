<?php
namespace Modules\Tenant\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantsTable extends Migration
{
    public function up()
    {
        // Define campos para a tabela tenants.  Cada loja possui um nome,
        // domínio (ou subdomínio), plano e status.  O campo plan_id pode
        // referenciar uma tabela de planos futura.
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'domain' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'plan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'basic',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
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
        $this->forge->addUniqueKey('domain');
        $this->forge->createTable('tenants');
    }

    public function down()
    {
        $this->forge->dropTable('tenants');
    }
}