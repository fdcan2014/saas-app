<?php
namespace Modules\Purchase\Repositories;

use Modules\Purchase\Entities\PurchaseEntity;
use Modules\Purchase\Entities\PurchaseItemEntity;

/**
 * Repositório para acesso às compras no banco de dados.
 */
class PurchaseRepository
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria uma nova compra e itens de compra.
     *
     * @param array $purchaseData Dados da compra
     * @param array $itemsData Lista de dados de itens
     */
    public function create(array $purchaseData, array $itemsData): PurchaseEntity
    {
        $this->db->transStart();
        $this->db->table('purchases')->insert($purchaseData);
        $purchaseId = $this->db->insertID();
        $items      = [];
        foreach ($itemsData as $item) {
            $item['purchase_id'] = $purchaseId;
            $this->db->table('purchase_items')->insert($item);
            $item['id'] = $this->db->insertID();
            $items[]    = new PurchaseItemEntity($item);
        }
        $this->db->transComplete();
        $purchaseData['id']    = $purchaseId;
        $purchaseData['items'] = $items;
        return new PurchaseEntity($purchaseData);
    }

    /**
     * Lista todas as compras de um tenant (sem itens).
     *
     * @return PurchaseEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('purchases')
            ->where('tenant_id', $tenantId)
            ->get()->getResult();
        return array_map(function ($row) {
            return new PurchaseEntity((array) $row);
        }, $rows);
    }

    /**
     * Recupera uma compra com seus itens.
     */
    public function find(int $id, int $tenantId): ?PurchaseEntity
    {
        $row = $this->db->table('purchases')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        if (! $row) {
            return null;
        }
        $purchaseData = (array) $row;
        // Carrega itens
        $itemsRows = $this->db->table('purchase_items')
            ->where('purchase_id', $id)
            ->get()->getResult();
        $items = [];
        foreach ($itemsRows as $it) {
            $items[] = new PurchaseItemEntity((array) $it);
        }
        $purchaseData['items'] = $items;
        return new PurchaseEntity($purchaseData);
    }

    /**
     * Atualiza campos de uma compra (como status).
     */
    public function update(int $id, int $tenantId, array $data): ?PurchaseEntity
    {
        $this->db->table('purchases')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        return $this->find($id, $tenantId);
    }

    /**
     * Exclui uma compra e seus itens.
     */
    public function delete(int $id, int $tenantId): bool
    {
        $this->db->transStart();
        // Remove itens
        $this->db->table('purchase_items')
            ->where('purchase_id', $id)
            ->delete();
        // Remove compra
        $this->db->table('purchases')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
        $this->db->transComplete();
        return $this->db->transStatus();
    }
}