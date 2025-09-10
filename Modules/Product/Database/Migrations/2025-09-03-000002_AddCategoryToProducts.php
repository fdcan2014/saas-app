<?php
namespace Modules\Product\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryToProducts extends Migration
{
    public function up()
    {
        // Adiciona coluna category_id à tabela de produtos
        $fields = [
            'category_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'tenant_id',
            ],
        ];
        $this->forge->addColumn('products', $fields);
        // Cria índice e chave estrangeira
        $this->forge->addKey('category_id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        // Remove chave estrangeira antes de remover coluna
        $this->forge->dropForeignKey('products', 'products_category_id_foreign');
        $this->forge->dropColumn('products', 'category_id');
    }
}