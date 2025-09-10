<?php
namespace Modules\Order\Services;

use Modules\Order\Repositories\OrderRepository;
use Modules\Order\Entities\OrderEntity;
use Modules\Product\Repositories\ProductRepository;
use Modules\Customer\Repositories\CustomerRepository;
use RuntimeException;

/**
 * Serviço de regras de negócio para pedidos de venda.
 */
class OrderService
{
    protected OrderRepository $orderRepo;
    protected ProductRepository $productRepo;
    protected CustomerRepository $customerRepo;

    public function __construct(OrderRepository $orderRepo, ProductRepository $productRepo, CustomerRepository $customerRepo)
    {
        $this->orderRepo   = $orderRepo;
        $this->productRepo = $productRepo;
        $this->customerRepo = $customerRepo;
    }

    /**
     * Lista pedidos de um tenant.
     *
     * @return OrderEntity[]
     */
    public function list(int $tenantId): array
    {
        return $this->orderRepo->findAllByTenant($tenantId);
    }

    /**
     * Recupera um pedido com itens.
     */
    public function get(int $id, int $tenantId): ?OrderEntity
    {
        return $this->orderRepo->find($id, $tenantId);
    }

    /**
     * Cria um novo pedido.
     *
     * Estrutura de $data:
     * - customer_id: opcional
     * - items: array de itens, cada um com product_id, quantity e opcionalmente discount
     */
    public function create(int $tenantId, array $data): OrderEntity
    {
        // Verifica cliente se informado
        $customerId = $data['customer_id'] ?? null;
        if ($customerId) {
            $customer = $this->customerRepo->find($customerId, $tenantId);
            if (! $customer) {
                throw new RuntimeException('Cliente não encontrado para este tenant');
            }
        }
        $itemsInput = $data['items'] ?? [];
        if (empty($itemsInput)) {
            throw new RuntimeException('Nenhum item informado no pedido');
        }
        $itemsData = [];
        $total     = 0.0;
        // Processa cada item
        foreach ($itemsInput as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                throw new RuntimeException('Item inválido: product_id e quantity são obrigatórios');
            }
            $productId = (int) $item['product_id'];
            $quantity  = (int) $item['quantity'];
            if ($quantity <= 0) {
                throw new RuntimeException('Quantidade do item deve ser maior que zero');
            }
            $product = $this->productRepo->find($productId, $tenantId);
            if (! $product) {
                throw new RuntimeException('Produto não encontrado ou não pertence a este tenant');
            }
            // Verifica estoque
            if ($product->stock_quantity < $quantity) {
                throw new RuntimeException('Estoque insuficiente para o produto: ' . $product->name);
            }
            $price    = $product->price;
            $discount = isset($item['discount']) ? (float) $item['discount'] : 0.0;
            $lineTotal = ($price * $quantity) - $discount;
            $total += $lineTotal;
            $itemsData[] = [
                'product_id' => $productId,
                'quantity'   => $quantity,
                'price'      => $price,
                'discount'   => $discount,
                'total'      => $lineTotal,
            ];
        }
        // Prepara dados da order
        $orderData = [
            'tenant_id'   => $tenantId,
            'customer_id' => $customerId,
            'status'      => $data['status'] ?? 'pending',
            'total'       => $total,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        // Atualiza estoque dos produtos
        foreach ($itemsData as $it) {
            $product = $this->productRepo->find($it['product_id'], $tenantId);
            $newQty  = $product->stock_quantity - $it['quantity'];
            $this->productRepo->update($product->id, $tenantId, ['stock_quantity' => $newQty]);
        }
        // Cria order e itens
        return $this->orderRepo->create($orderData, $itemsData);
    }

    /**
     * Atualiza status de um pedido.
     */
    public function updateStatus(int $id, int $tenantId, string $status): ?OrderEntity
    {
        $order = $this->orderRepo->find($id, $tenantId);
        if (! $order) {
            return null;
        }
        return $this->orderRepo->update($id, $tenantId, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Exclui um pedido.
     */
    public function delete(int $id, int $tenantId): bool
    {
        // TODO: Retornar estoque? Neste ponto vamos apenas deletar.
        return $this->orderRepo->delete($id, $tenantId);
    }
}