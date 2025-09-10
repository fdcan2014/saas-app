<?php
namespace Modules\Customer\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $builder = $this->db->table('customers');
        // Cliente de exemplo para o tenant 1
        $builder->insert([
            'tenant_id'  => 1,
            'name'       => 'Cliente Exemplo',
            'email'      => 'cliente@exemplo.com',
            'phone'      => '(11) 98765-4321',
            'tax_id'     => '123.456.789-09',
            'address'    => 'Rua Exemplo, 123, Centro, Cidade/UF',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}