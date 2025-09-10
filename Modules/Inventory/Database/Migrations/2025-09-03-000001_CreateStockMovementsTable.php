<?php
namespace Modules\Inventory\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criação da tabela de movimentações de estoque.
 *
 * Cada registro representa um ajuste de estoque (entrada ou saída) e registra a origem
 * da movimentação, permitindo reconstruir o histórico de quantidade de produtos.
 */
class CreateStockMovementsTable extends Migration
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
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'comment'    => 'in ou out',
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
                'null'       => true,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('product_id');
        $this->forge->createTable('stock_movements');
    }

    public function down(): void
    {
        $this->forge->dropTable('stock_movements');
    }
}