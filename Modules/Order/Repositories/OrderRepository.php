<?php
namespace Modules\Order\Repositories;

use Modules\Order\Entities\OrderEntity;
use Modules\Order\Entities\OrderItemEntity;

/**
 * Repositório para acesso a pedidos.
 */
class OrderRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria um pedido e seus itens em transação.
     *
     * @param array $orderData  Dados da tabela orders.
     * @param array $itemsData  Lista de itens: cada item deve conter product_id, quantity, price, discount, total.
     */
    public function create(array $orderData, array $itemsData): OrderEntity
    {
        $this->db->transStart();
        $this->db->table('orders')->insert($orderData);
        $orderId = $this->db->insertID();
        foreach ($itemsData as $item) {
            $item['order_id'] = $orderId;
            $this->db->table('order_items')->insert($item);
        }
        $this->db->transComplete();
        $orderData['id']   = $orderId;
        $orderData['items'] = array_map(fn ($item) => new OrderItemEntity($item + ['order_id' => $orderId]), $itemsData);
        return new OrderEntity($orderData);
    }

    /**
     * Retorna todos os pedidos de um tenant.
     * Não carrega itens para performance.
     *
     * @return OrderEntity[]
     */
    public function findAllByTenant(int $tenantId): array
    {
        $rows = $this->db->table('orders')->where('tenant_id', $tenantId)->get()->getResult();
        return array_map(fn ($row) => new OrderEntity((array) $row), $rows);
    }

    /**
     * Encontra um pedido com seus itens.
     */
    public function find(int $id, int $tenantId): ?OrderEntity
    {
        $orderRow = $this->db->table('orders')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();
        if (! $orderRow) {
            return null;
        }
        $order = new OrderEntity((array) $orderRow);
        // Recupera itens
        $items = $this->db->table('order_items')->where('order_id', $id)->get()->getResult();
        $order->items = array_map(fn ($row) => new OrderItemEntity((array) $row), $items);
        return $order;
    }

    /**
     * Atualiza dados do pedido.
     * Somente campos da tabela orders são tratados.
     */
    public function update(int $id, int $tenantId, array $data): ?OrderEntity
    {
        $this->db->table('orders')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update($data);
        return $this->find($id, $tenantId);
    }

    /**
     * Remove um pedido e seus itens.
     */
    public function delete(int $id, int $tenantId): bool
    {
        $this->db->transStart();
        // Exclui itens
        $this->db->table('order_items')->where('order_id', $id)->delete();
        // Exclui pedido
        $affected = $this->db->table('orders')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
        $this->db->transComplete();
        return (bool) $affected;
    }
}