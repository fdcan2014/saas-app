<?php
namespace Modules\PurchaseReturn\Repositories;

use Modules\PurchaseReturn\Entities\PurchaseReturnEntity;
use Modules\PurchaseReturn\Entities\PurchaseReturnItemEntity;

/**
 * Repositório para devoluções de compras.
 */
class PurchaseReturnRepository
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria uma devolução de compra e seus itens.
     */
    public function create(array $data, array $itemsData): PurchaseReturnEntity
    {
        $this->db->transStart();
        $this->db->table('purchase_returns')->insert($data);
        $returnId = $this->db->insertID();
        $items    = [];
        foreach ($itemsData as $item) {
            $item['purchase_return_id'] = $returnId;
            $this->db->table('purchase_return_items')->insert($item);
            $item['id'] = $this->db->insertID();
            $items[] = new PurchaseReturnItemEntity($item);
        }
        $this->db->transComplete();
        $data['id']    = $returnId;
        $data['items'] = $items;
        return new PurchaseReturnEntity($data);
    }

    /**
     * Lista devoluções de compras por tenant (sem itens).
     *
     * @return PurchaseReturnEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('purchase_returns')->where('tenant_id', $tenantId)->get()->getResult();
        return array_map(fn ($row) => new PurchaseReturnEntity((array) $row), $rows);
    }

    /**
     * Recupera uma devolução de compra com itens.
     */
    public function find(int $id, int $tenantId): ?PurchaseReturnEntity
    {
        $row = $this->db->table('purchase_returns')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        if (! $row) {
            return null;
        }
        $returnData = (array) $row;
        $itemsRows = $this->db->table('purchase_return_items')
            ->where('purchase_return_id', $id)
            ->get()->getResult();
        $items = [];
        foreach ($itemsRows as $it) {
            $items[] = new PurchaseReturnItemEntity((array) $it);
        }
        $returnData['items'] = $items;
        return new PurchaseReturnEntity($returnData);
    }
}