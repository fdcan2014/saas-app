<?php
namespace Modules\SalesReturn\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criação da tabela de itens das devoluções de vendas.
 */
class CreateSalesReturnItemsTable extends Migration
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
            'sales_return_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sales_return_id');
        $this->forge->addKey('product_id');
        $this->forge->createTable('sales_return_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('sales_return_items');
    }
}