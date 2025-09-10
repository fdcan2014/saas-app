<?php
namespace Modules\PurchaseReturn\Services;

use Modules\PurchaseReturn\Repositories\PurchaseReturnRepository;
use Modules\PurchaseReturn\Entities\PurchaseReturnEntity;
use Modules\Product\Repositories\ProductRepository;
use Modules\Purchase\Repositories\PurchaseRepository;
use RuntimeException;

/**
 * Serviço de regras de negócio para devoluções de compras.
 */
class PurchaseReturnService
{
    protected PurchaseReturnRepository $returnRepo;
    protected ProductRepository $productRepo;
    protected PurchaseRepository $purchaseRepo;

    public function __construct(PurchaseReturnRepository $returnRepo, ProductRepository $productRepo, PurchaseRepository $purchaseRepo)
    {
        $this->returnRepo  = $returnRepo;
        $this->productRepo = $productRepo;
        $this->purchaseRepo = $purchaseRepo;
    }

    /**
     * Lista devoluções de compras do tenant.
     *
     * @return PurchaseReturnEntity[]
     */
    public function list(int $tenantId): array
    {
        return $this->returnRepo->findAllByTenant($tenantId);
    }

    /**
     * Recupera uma devolução de compra.
     */
    public function get(int $id, int $tenantId): ?PurchaseReturnEntity
    {
        return $this->returnRepo->find($id, $tenantId);
    }

    /**
     * Cria uma devolução de compra.
     *
     * Estrutura de $data:
     * - purchase_id: obrigatório
     * - items: array com product_id, quantity e opcionalmente discount
     */
    public function create(int $tenantId, array $data): PurchaseReturnEntity
    {
        $purchaseId = $data['purchase_id'] ?? null;
        if (! $purchaseId) {
            throw new RuntimeException('purchase_id é obrigatório');
        }
        $purchase = $this->purchaseRepo->find($purchaseId, $tenantId);
        if (! $purchase) {
            throw new RuntimeException('Compra não encontrada para este tenant');
        }
        $itemsInput = $data['items'] ?? [];
        if (empty($itemsInput)) {
            throw new RuntimeException('Nenhum item informado na devolução');
        }
        $itemsData = [];
        $total     = 0.0;
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
            $total += $lineTotal;
            $itemsData[] = [
                'product_id' => $productId,
                'quantity'   => $quantity,
                'price'      => $price,
                'discount'   => $discount,
                'total'      => $lineTotal,
            ];
        }
        // Ajusta estoque (remove quantidade devolvida ao fornecedor)
        foreach ($itemsData as $it) {
            $product = $this->productRepo->find($it['product_id'], $tenantId);
            // Verifica se há estoque suficiente para devolver
            if ($product->stock_quantity < $it['quantity']) {
                throw new RuntimeException('Estoque insuficiente para devolver o produto: ' . $product->name);
            }
            $newQty = $product->stock_quantity - $it['quantity'];
            $this->productRepo->update($product->id, $tenantId, ['stock_quantity' => $newQty]);
        }
        $returnData = [
            'tenant_id'   => $tenantId,
            'purchase_id' => $purchaseId,
            'status'      => $data['status'] ?? 'pending',
            'total'       => $total,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        return $this->returnRepo->create($returnData, $itemsData);
    }
}