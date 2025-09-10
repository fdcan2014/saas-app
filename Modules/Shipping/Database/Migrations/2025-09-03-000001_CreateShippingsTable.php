<?php
namespace Modules\Shipping\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criação da tabela de envios (shippings).
 */
class CreateShippingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 9,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'address' => [
                'type' => 'TEXT',
            ],
            'carrier' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tracking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'pending',
            ],
            'shipped_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('order_id');
        $this->forge->createTable('shippings');
    }

    public function down(): void
    {
        $this->forge->dropTable('shippings');
    }
}