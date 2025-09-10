<?php
namespace Modules\Customer\Services;

use Modules\Customer\Repositories\CustomerRepository;
use Modules\Customer\Entities\CustomerEntity;
use RuntimeException;

/**
 * Serviço contendo regras de negócio para clientes.
 */
class CustomerService
{
    protected CustomerRepository $repo;

    public function __construct(CustomerRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Lista todos os clientes de um tenant.
     *
     * @param int $tenantId
     * @return CustomerEntity[]
     */
    public function list(int $tenantId): array
    {
        return $this->repo->findAllByTenant($tenantId);
    }

    /**
     * Cria um novo cliente.
     *
     * @param int   $tenantId
     * @param array $data Deve conter nome (name) e opcionalmente email, phone, tax_id, address.
     */
    public function create(int $tenantId, array $data): CustomerEntity
    {
        if (empty($data['name'])) {
            throw new RuntimeException('Nome do cliente é obrigatório');
        }
        // Normaliza email para minúsculo
        if (! empty($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
            // Verifica email único no tenant
            $customers = $this->repo->findAllByTenant($tenantId);
            foreach ($customers as $c) {
                if ($c->email && strtolower($c->email) === $data['email']) {
                    throw new RuntimeException('E-mail já cadastrado neste tenant');
                }
            }
        }
        // Verifica CPF/CNPJ único
        if (! empty($data['tax_id'])) {
            $data['tax_id'] = preg_replace('/\D+/', '', $data['tax_id']);
            $customers = $customers ?? $this->repo->findAllByTenant($tenantId);
            foreach ($customers as $c) {
                $existingTax = $c->tax_id ? preg_replace('/\D+/', '', $c->tax_id) : '';
                if ($existingTax && $existingTax === $data['tax_id']) {
                    throw new RuntimeException('CPF/CNPJ já cadastrado neste tenant');
                }
            }
        }
        // Prepara dados de inserção
        $insert = [
            'tenant_id'  => $tenantId,
            'name'       => $data['name'],
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'tax_id'     => $data['tax_id'] ?? null,
            'address'    => $data['address'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return $this->repo->create($insert);
    }

    /**
     * Atualiza um cliente existente.
     */
    public function update(int $id, int $tenantId, array $data): ?CustomerEntity
    {
        $customer = $this->repo->find($id, $tenantId);
        if (! $customer) {
            return null;
        }
        $updateData = [];
        if (isset($data['name'])) {
            if (empty($data['name'])) {
                throw new RuntimeException('Nome não pode ser vazio');
            }
            $updateData['name'] = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $email = $data['email'];
            if ($email) {
                $email = strtolower(trim($email));
                // Verifica duplicidade
                $customers = $this->repo->findAllByTenant($tenantId);
                foreach ($customers as $c) {
                    if ($c->email && strtolower($c->email) === $email && $c->id !== $id) {
                        throw new RuntimeException('E-mail já cadastrado neste tenant');
                    }
                }
                $updateData['email'] = $email;
            } else {
                $updateData['email'] = null;
            }
        }
        if (array_key_exists('tax_id', $data)) {
            $taxId = $data['tax_id'];
            if ($taxId) {
                $normalized = preg_replace('/\D+/', '', $taxId);
                $customers = $customers ?? $this->repo->findAllByTenant($tenantId);
                foreach ($customers as $c) {
                    $existingTax = $c->tax_id ? preg_replace('/\D+/', '', $c->tax_id) : '';
                    if ($existingTax && $existingTax === $normalized && $c->id !== $id) {
                        throw new RuntimeException('CPF/CNPJ já cadastrado neste tenant');
                    }
                }
                $updateData['tax_id'] = $taxId;
            } else {
                $updateData['tax_id'] = null;
            }
        }
        if (array_key_exists('phone', $data)) {
            $updateData['phone'] = $data['phone'] ?: null;
        }
        if (array_key_exists('address', $data)) {
            $updateData['address'] = $data['address'] ?: null;
        }
        if (! empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->repo->update($id, $tenantId, $updateData);
        }
        return $customer;
    }

    /**
     * Remove um cliente.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->repo->delete($id, $tenantId);
    }

    /**
     * Recupera um cliente.
     */
    public function get(int $id, int $tenantId): ?CustomerEntity
    {
        return $this->repo->find($id, $tenantId);
    }
}