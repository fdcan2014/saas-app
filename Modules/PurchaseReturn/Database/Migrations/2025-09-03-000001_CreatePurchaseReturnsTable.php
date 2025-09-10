<?php
namespace Modules\PurchaseReturn\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criação da tabela de devoluções de compras.
 */
class CreatePurchaseReturnsTable extends Migration
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
            'purchase_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'pending',
            ],
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
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
        $this->forge->addKey('purchase_id');
        $this->forge->createTable('purchase_returns');
    }

    public function down(): void
    {
        $this->forge->dropTable('purchase_returns');
    }
}