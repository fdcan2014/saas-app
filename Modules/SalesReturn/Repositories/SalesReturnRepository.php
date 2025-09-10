<?php
namespace Modules\SalesReturn\Repositories;

use Modules\SalesReturn\Entities\SalesReturnEntity;
use Modules\SalesReturn\Entities\SalesReturnItemEntity;

/**
 * Repositório para devoluções de vendas.
 */
class SalesReturnRepository
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria uma devolução e seus itens.
     *
     * @param array $data Dados da devolução
     * @param array $itemsData Itens da devolução
     */
    public function create(array $data, array $itemsData): SalesReturnEntity
    {
        $this->db->transStart();
        $this->db->table('sales_returns')->insert($data);
        $returnId = $this->db->insertID();
        $items    = [];
        foreach ($itemsData as $item) {
            $item['sales_return_id'] = $returnId;
            $this->db->table('sales_return_items')->insert($item);
            $item['id'] = $this->db->insertID();
            $items[] = new SalesReturnItemEntity($item);
        }
        $this->db->transComplete();
        $data['id']    = $returnId;
        $data['items'] = $items;
        return new SalesReturnEntity($data);
    }

    /**
     * Lista devoluções por tenant (sem itens).
     *
     * @return SalesReturnEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('sales_returns')
            ->where('tenant_id', $tenantId)
            ->get()->getResult();
        return array_map(fn ($row) => new SalesReturnEntity((array) $row), $rows);
    }

    /**
     * Recupera uma devolução com itens.
     */
    public function find(int $id, int $tenantId): ?SalesReturnEntity
    {
        $row = $this->db->table('sales_returns')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        if (! $row) {
            return null;
        }
        $returnData = (array) $row;
        $itemsRows = $this->db->table('sales_return_items')
            ->where('sales_return_id', $id)
            ->get()->getResult();
        $items = [];
        foreach ($itemsRows as $it) {
            $items[] = new SalesReturnItemEntity((array) $it);
        }
        $returnData['items'] = $items;
        return new SalesReturnEntity($returnData);
    }
}