<?php
namespace Modules\Purchase\Services;

use Modules\Purchase\Repositories\PurchaseRepository;
use Modules\Purchase\Entities\PurchaseEntity;
use Modules\Product\Repositories\ProductRepository;
use Modules\Supplier\Repositories\SupplierRepository;
use RuntimeException;

/**
 * Serviço de regras de negócio para compras.
 *
 * Responsável por validar fornecedores, produtos e calcular totais, além de atualizar
 * o estoque dos produtos comprados.
 */
class PurchaseService
{
    protected PurchaseRepository $purchaseRepo;
    protected ProductRepository $productRepo;
    protected SupplierRepository $supplierRepo;

    public function __construct(PurchaseRepository $purchaseRepo, ProductRepository $productRepo, SupplierRepository $supplierRepo)
    {
        $this->purchaseRepo = $purchaseRepo;
        $this->productRepo  = $productRepo;
        $this->supplierRepo = $supplierRepo;
    }

    /**
     * Lista compras de um tenant.
     *
     * @return PurchaseEntity[]
     */
    public function list(int $tenantId): array
    {
        return $this->purchaseRepo->findAllByTenant($tenantId);
    }

    /**
     * Recupera uma compra e seus itens.
     */
    public function get(int $id, int $tenantId): ?PurchaseEntity
    {
        return $this->purchaseRepo->find($id, $tenantId);
    }

    /**
     * Cria uma compra.
     *
     * Estrutura de $data:
     * - supplier_id: obrigatório
     * - items: array de itens, cada item com product_id, quantity e opcionalmente discount
     */
    public function create(int $tenantId, array $data): PurchaseEntity
    {
        $supplierId = $data['supplier_id'] ?? null;
        if (! $supplierId) {
            throw new RuntimeException('Fornecedor não informado');
        }
        // Verifica se fornecedor pertence ao tenant
        $supplier = $this->supplierRepo->find($supplierId, $tenantId);
        if (! $supplier) {
            throw new RuntimeException('Fornecedor não encontrado ou não pertence a este tenant');
        }
        $itemsInput = $data['items'] ?? [];
        if (empty($itemsInput)) {
            throw new RuntimeException('Nenhum item informado na compra');
        }
        $itemsData = [];
        $total     = 0.0;
        // Para cada item, valida produto e calcula totais
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
        // Dados da compra
        $purchaseData = [
            'tenant_id'   => $tenantId,
            'supplier_id' => $supplierId,
            'status'      => $data['status'] ?? 'pending',
            'total'       => $total,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        // Atualiza estoque (adiciona quantidade)
        foreach ($itemsData as $it) {
            $product = $this->productRepo->find($it['product_id'], $tenantId);
            $newQty  = $product->stock_quantity + $it['quantity'];
            $this->productRepo->update($product->id, $tenantId, ['stock_quantity' => $newQty]);
        }
        // Cria compra e retorna entidade
        return $this->purchaseRepo->create($purchaseData, $itemsData);
    }

    /**
     * Atualiza status da compra.
     */
    public function updateStatus(int $id, int $tenantId, string $status): ?PurchaseEntity
    {
        $purchase = $this->purchaseRepo->find($id, $tenantId);
        if (! $purchase) {
            return null;
        }
        $updateData = [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        return $this->purchaseRepo->update($id, $tenantId, $updateData);
    }

    /**
     * Remove uma compra e seus itens.
     */
    public function delete(int $id, int $tenantId): bool
    {
        // TODO: poderíamos reverter estoque aqui se houver cancelamento da compra
        return $this->purchaseRepo->delete($id, $tenantId);
    }
}