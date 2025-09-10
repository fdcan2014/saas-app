<?php
namespace Modules\Category\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $builder = $this->db->table('categories');
        $builder->insertBatch([
            [
                'tenant_id'  => 1,
                'name'       => 'Eletrônicos',
                'slug'       => 'eletronicos',
                'parent_id'  => null,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tenant_id'  => 1,
                'name'       => 'Roupas',
                'slug'       => 'roupas',
                'parent_id'  => null,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}