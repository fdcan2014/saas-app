<?php
namespace Modules\SalesReturn\Services;

use Modules\SalesReturn\Repositories\SalesReturnRepository;
use Modules\SalesReturn\Entities\SalesReturnEntity;
use Modules\Product\Repositories\ProductRepository;
use Modules\Order\Repositories\OrderRepository;
use RuntimeException;

/**
 * Serviço de regras de negócio para devoluções de vendas.
 */
class SalesReturnService
{
    protected SalesReturnRepository $returnRepo;
    protected ProductRepository $productRepo;
    protected OrderRepository $orderRepo;

    public function __construct(SalesReturnRepository $returnRepo, ProductRepository $productRepo, OrderRepository $orderRepo)
    {
        $this->returnRepo  = $returnRepo;
        $this->productRepo = $productRepo;
        $this->orderRepo   = $orderRepo;
    }

    /**
     * Lista devoluções do tenant.
     *
     * @return SalesReturnEntity[]
     */
    public function list(int $tenantId): array
    {
        return $this->returnRepo->findAllByTenant($tenantId);
    }

    /**
     * Recupera uma devolução específica.
     */
    public function get(int $id, int $tenantId): ?SalesReturnEntity
    {
        return $this->returnRepo->find($id, $tenantId);
    }

    /**
     * Cria uma devolução de venda.
     *
     * Estrutura de $data:
     * - order_id: obrigatório
     * - items: array com product_id, quantity e opcionalmente discount
     */
    public function create(int $tenantId, array $data): SalesReturnEntity
    {
        $orderId = $data['order_id'] ?? null;
        if (! $orderId) {
            throw new RuntimeException('order_id é obrigatório');
        }
        $order = $this->orderRepo->find($orderId, $tenantId);
        if (! $order) {
            throw new RuntimeException('Pedido não encontrado para este tenant');
        }
        $itemsInput = $data['items'] ?? [];
        if (empty($itemsInput)) {
            throw new RuntimeException('Nenhum item informado na devolução');
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
                throw new RuntimeException('Quantidade deve ser maior que zero');
            }
            $product = $this->productRepo->find($productId, $tenantId);
            if (! $product) {
                throw new RuntimeException('Produto não encontrado ou não pertence a este tenant');
            }
            $price    = $product->price;
            $discount = isset($item['discount']) ? (float) $item['discount'] : 0.0;
            $lineTotal = ($price * $quantity) - $discount;
            $total    += $lineTotal;
            $itemsData[] = [
                'product_id' => $productId,
                'quantity'   => $quantity,
                'price'      => $price,
                'discount'   => $discount,
                'total'      => $lineTotal,
            ];
        }
        // Ajusta estoque (adiciona quantidade devolvida)
        foreach ($itemsData as $it) {
            $product = $this->productRepo->find($it['product_id'], $tenantId);
            $newQty  = $product->stock_quantity + $it['quantity'];
            $this->productRepo->update($product->id, $tenantId, ['stock_quantity' => $newQty]);
        }
        $returnData = [
            'tenant_id'  => $tenantId,
            'order_id'   => $orderId,
            'status'     => $data['status'] ?? 'pending',
            'total'      => $total,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return $this->returnRepo->create($returnData, $itemsData);
    }
}