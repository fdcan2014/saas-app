<?php
namespace Modules\Tenant\Services;

use Modules\Tenant\Repositories\TenantRepository;
use Modules\Tenant\Entities\TenantEntity;

/**
 * Serviço para resolução e gestão de tenants.
 */
class TenantService
{
    protected TenantRepository $repo;

    public function __construct(TenantRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Retorna um tenant específico pelo ID.
     */
    public function get(int $id): ?TenantEntity
    {
        return $this->repo->find($id);
    }

    /**
     * Resolve um tenant a partir de um domínio (subdomínio) ou header.
     */
    public function resolveTenant(string $hostOrHeader): ?TenantEntity
    {
        // Se o cabeçalho ou parâmetro for numérico, trata como ID direto
        if (is_numeric($hostOrHeader)) {
            return $this->repo->find((int) $hostOrHeader);
        }
        // Se contiver um ponto, assume ser um domínio/subdomínio e remove a porção do host principal
        $domain = strtolower($hostOrHeader);
        // Remova a porta caso exista
        $parts = explode(':', $domain);
        $domain = $parts[0];
        // Consultar diretamente por domínio
        return $this->repo->findByDomain($domain);
    }

    /**
     * Lista todos os tenants.
     *
     * @return TenantEntity[]
     */
    public function all(): array
    {
        return $this->repo->findAll();
    }

    /**
     * Cria uma nova loja a partir dos dados informados.
     */
    public function create(array $data): TenantEntity
    {
        // Verifica se já existe um domínio igual
        if (! empty($data['domain'])) {
            $existing = $this->repo->findByDomain($data['domain']);
            if ($existing) {
                throw new \RuntimeException('Domínio já em uso por outro tenant');
            }
        }
        // Define valores padrão
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->repo->create($data);
    }

    /**
     * Atualiza uma loja existente.
     */
    public function update(int $id, array $data): ?TenantEntity
    {
        // Se o domínio for alterado, verifica duplicidade
        if (isset($data['domain'])) {
            $existing = $this->repo->findByDomain($data['domain']);
            if ($existing && $existing->id !== $id) {
                throw new \RuntimeException('Domínio já em uso por outro tenant');
            }
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->repo->update($id, $data);
    }

    /**
     * Remove uma loja pelo ID.
     */
    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }
}