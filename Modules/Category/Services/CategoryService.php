<?php
namespace Modules\Category\Services;

use Modules\Category\Repositories\CategoryRepository;
use Modules\Category\Entities\CategoryEntity;
use RuntimeException;

/**
 * Serviço contendo as regras de negócio para categorias de produtos.
 *
 * Ele valida nomes e slugs, garante unicidade por tenant e verifica
 * a integridade do relacionamento pai/filho.
 */
class CategoryService
{
    protected CategoryRepository $repo;

    public function __construct(CategoryRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Lista todas as categorias de um tenant.
     *
     * @param int $tenantId
     * @return CategoryEntity[]
     */
    public function list(int $tenantId): array
    {
        return $this->repo->findAllByTenant($tenantId);
    }

    /**
     * Cria uma nova categoria.
     *
     * @param int   $tenantId
     * @param array $data Deve conter pelo menos o campo `name`. Pode conter `slug` e `parent_id`.
     */
    public function create(int $tenantId, array $data): CategoryEntity
    {
        // Validação básica
        if (empty($data['name'])) {
            throw new RuntimeException('Nome da categoria é obrigatório');
        }

        // Gera slug se não informado
        $slug = $data['slug'] ?? $this->slugify($data['name']);

        // Verifica se o slug já existe para este tenant
        $existing = $this->repo->findAllByTenant($tenantId);
        foreach ($existing as $cat) {
            if ($cat->slug === $slug) {
                throw new RuntimeException('Slug já utilizado neste tenant');
            }
        }

        // Verifica parent_id, se fornecido
        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parent = $this->repo->find($parentId, $tenantId);
            if (! $parent) {
                throw new RuntimeException('Categoria pai não encontrada para este tenant');
            }
        }

        // Prepara dados
        $insert = [
            'tenant_id'  => $tenantId,
            'name'       => $data['name'],
            'slug'       => $slug,
            'parent_id'  => $parentId,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return $this->repo->create($insert);
    }

    /**
     * Atualiza uma categoria existente.
     *
     * @param int   $id
     * @param int   $tenantId
     * @param array $data
     */
    public function update(int $id, int $tenantId, array $data): ?CategoryEntity
    {
        $category = $this->repo->find($id, $tenantId);
        if (! $category) {
            return null;
        }

        $updateData = [];
        // Atualiza nome
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $updateData['name'] = $data['name'];
            // Se slug não for fornecido explicitamente, atualiza o slug derivado
            if (! isset($data['slug'])) {
                $data['slug'] = $this->slugify($data['name']);
            }
        }
        // Slug fornecido
        if (isset($data['slug'])) {
            $slug = $data['slug'];
            // Verifica duplicidade
            $existing = $this->repo->findAllByTenant($tenantId);
            foreach ($existing as $cat) {
                if ($cat->slug === $slug && $cat->id !== $id) {
                    throw new RuntimeException('Slug já utilizado neste tenant');
                }
            }
            $updateData['slug'] = $slug;
        }
        // Parent
        if (array_key_exists('parent_id', $data)) {
            $parentId = $data['parent_id'];
            if ($parentId) {
                $parent = $this->repo->find($parentId, $tenantId);
                if (! $parent) {
                    throw new RuntimeException('Categoria pai não encontrada para este tenant');
                }
                // Evita relação circular
                if ($parentId === $id) {
                    throw new RuntimeException('Uma categoria não pode ser pai dela mesma');
                }
            }
            $updateData['parent_id'] = $parentId;
        }
        if (! empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->repo->update($id, $tenantId, $updateData);
        }
        return $category;
    }

    /**
     * Remove uma categoria.
     */
    public function delete(int $id, int $tenantId): bool
    {
        return $this->repo->delete($id, $tenantId);
    }

    /**
     * Recupera uma categoria pelo ID.
     */
    public function get(int $id, int $tenantId): ?CategoryEntity
    {
        return $this->repo->find($id, $tenantId);
    }

    /**
     * Converte um texto em um slug URL-amigável.
     *
     * Remove caracteres especiais, substitui espaços por hífens e converte para minúsculas.
     */
    protected function slugify(string $text): string
    {
        // Translitera para ASCII
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        // Remove caracteres não alfanuméricos, substituindo por espaço
        $text = preg_replace('/[^A-Za-z0-9]+/', ' ', $text);
        // Normaliza múltiplos espaços
        $text = trim($text);
        $text = preg_replace('/ +/', '-', $text);
        // Minúsculas
        return strtolower($text);
    }
}