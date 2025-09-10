<?php
namespace Modules\Supplier\Services;

use Modules\Supplier\Repositories\SupplierRepository;
use Modules\Supplier\Entities\SupplierEntity;
use RuntimeException;

/**
 * Serviço contendo regras de negócio para fornecedores.
 */
class SupplierService
{
    protected SupplierRepository $repo;

    public function __construct(SupplierRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Lista todos os fornecedores de um tenant.
     *
     * @return SupplierEntity[]
     */
    public function list(int $tenantId): array
    {
        return $this->repo->findAllByTenant($tenantId);
    }

    /**
     * Cria um novo fornecedor.
     */
    public function create(int $tenantId, array $data): SupplierEntity
    {
        if (empty($data['name'])) {
            throw new RuntimeException('Nome do fornecedor é obrigatório');
        }
        // Normaliza e-mail
        if (! empty($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
            $suppliers = $this->repo->findAllByTenant($tenantId);
            foreach ($suppliers as $s) {
                if ($s->email && strtolower($s->email) === $data['email']) {
                    throw new RuntimeException('E-mail já cadastrado neste tenant');
                }
            }
        }
        // Verifica tax_id (CNPJ) único
        if (! empty($data['tax_id'])) {
            $normalized = preg_replace('/\D+/', '', $data['tax_id']);
            $suppliers = $suppliers ?? $this->repo->findAllByTenant($tenantId);
            foreach ($suppliers as $s) {
                $existingTax = $s->tax_id ? preg_replace('/\D+/', '', $s->tax_id) : '';
                if ($existingTax && $existingTax === $normalized) {
                    throw new RuntimeException('CNPJ/CPF já cadastrado neste tenant');
                }
            }
        }
        $insert = [
            'tenant_id'  => $tenantId,
            'name'       => $data['name'],
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'tax_id'     => $data['tax_id'] ?? null,
            'contact'    => $data['contact'] ?? null,
            'address'    => $data['address'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return $this->repo->create($insert);
    }

    /**
     * Atualiza um fornecedor.
     */
    public function update(int $id, int $tenantId, array $data): ?SupplierEntity
    {
        $supplier = $this->repo->find($id, $tenantId);
        if (! $supplier) {
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
                $suppliers = $this->repo->findAllByTenant($tenantId);
                foreach ($suppliers as $s) {
                    if ($s->email && strtolower($s->email) === $email && $s->id !== $id) {
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
                $suppliers = $suppliers ?? $this->repo->findAllByTenant($tenantId);
                foreach ($suppliers as $s) {
                    $existingTax = $s->tax_id ? preg_replace('/\D+/', '', $s->tax_id) : '';
                    if ($existingTax && $existingTax === $normalized && $s->id !== $id) {
                        throw new RuntimeException('CNPJ/CPF já cadastrado neste tenant');
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
        if (array_key_exists('contact', $data)) {
            $updateData['contact'] = $data['contact'] ?: null;
        }
        if (array_key_exists('address', $data)) {
            $updateData['address'] = $data['address'] ?: null;
        }
        if (! empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->repo->update($id, $tenantId, $updateData);
        }
        return $supplier;
    }

    /**
     * Remove um fornecedor.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->repo->delete($id, $tenantId);
    }

    /**
     * Recupera um fornecedor.
     */
    public function get(int $id, int $tenantId): ?SupplierEntity
    {
        return $this->repo->find($id, $tenantId);
    }
}