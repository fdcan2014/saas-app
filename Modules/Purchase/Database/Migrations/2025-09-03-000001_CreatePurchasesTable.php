<?php
namespace Modules\Purchase\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criação da tabela de compras.
 *
 * Armazena os pedidos de compra realizados pelos tenants junto aos fornecedores.
 */
class CreatePurchasesTable extends Migration
{
    public function up(): void
    {
        // Define campos da tabela
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
            'supplier_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'pending',
            ],
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        // Define chave primária
        $this->forge->addKey('id', true);
        // Índices para performance
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('supplier_id');
        // Cria tabela
        $this->forge->createTable('purchases');
    }

    public function down(): void
    {
        $this->forge->dropTable('purchases');
    }
}