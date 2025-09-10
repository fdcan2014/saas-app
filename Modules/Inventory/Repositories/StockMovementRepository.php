<?php
namespace Modules\Inventory\Repositories;

use Modules\Inventory\Entities\StockMovementEntity;

/**
 * Repositório responsável por persistir e recuperar movimentações de estoque.
 */
class StockMovementRepository
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria um novo registro de movimentação de estoque.
     */
    public function create(array $data): StockMovementEntity
    {
        $this->db->table('stock_movements')->insert($data);
        $data['id'] = $this->db->insertID();
        return new StockMovementEntity($data);
    }

    /**
     * Lista as movimentações por tenant. Pode filtrar por produto.
     *
     * @param int      $tenantId
     * @param int|null $productId
     * @return StockMovementEntity[]
     */
    public function findByTenant(int $tenantId, ?int $productId = null): array
    {
        $builder = $this->db->table('stock_movements')->where('tenant_id', $tenantId);
        if ($productId) {
            $builder->where('product_id', $productId);
        }
        $rows = $builder->orderBy('created_at', 'DESC')->get()->getResult();
        return array_map(fn ($row) => new StockMovementEntity((array) $row), $rows);
    }
}