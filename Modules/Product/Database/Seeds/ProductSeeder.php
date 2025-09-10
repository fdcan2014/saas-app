<?php
namespace Modules\Product\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $builder = $this->db->table('products');
        // Produto de exemplo para tenant id=1
        $builder->insert([
            'tenant_id'      => 1,
            'name'           => 'Produto Exemplo',
            'sku'            => 'PROD-001',
            'description'    => 'Um produto de demonstração.',
            'price'          => 99.99,
            'stock_quantity' => 10,
            'category_id'    => 1,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}