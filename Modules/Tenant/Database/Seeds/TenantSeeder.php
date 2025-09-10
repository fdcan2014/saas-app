<?php
namespace Modules\Tenant\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $builder = $this->db->table('tenants');
        // Inserir um tenant padrão se ainda não existir
        $builder->ignore(true)->insert([
            'id'         => 1,
            'name'       => 'Loja Padrão',
            'domain'     => 'loja.localhost',
            'plan'       => 'basic',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}