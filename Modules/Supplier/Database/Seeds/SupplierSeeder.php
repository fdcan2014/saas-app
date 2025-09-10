<?php
namespace Modules\Supplier\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $builder = $this->db->table('suppliers');
        $builder->insert([
            'tenant_id'  => 1,
            'name'       => 'Fornecedor Exemplo',
            'email'      => 'fornecedor@exemplo.com',
            'phone'      => '(11) 3333-4444',
            'tax_id'     => '12.345.678/0001-99',
            'contact'    => 'Fulano de Tal',
            'address'    => 'Av. dos Fornecedores, 100, Bairro, Cidade/UF',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}