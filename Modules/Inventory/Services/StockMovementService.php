<?php
namespace Modules\Inventory\Services;

use Modules\Inventory\Repositories\StockMovementRepository;
use Modules\Product\Repositories\ProductRepository;
use Modules\Inventory\Entities\StockMovementEntity;
use RuntimeException;

/**
 * Serviço para registrar movimentações de estoque e atualizar a quantidade de produtos.
 */
class StockMovementService
{
    protected StockMovementRepository $movementRepo;
    protected ProductRepository $productRepo;

    public function __construct(StockMovementRepository $movementRepo, ProductRepository $productRepo)
    {
        $this->movementRepo = $movementRepo;
        $this->productRepo  = $productRepo;
    }

    /**
     * Registra uma movimentação de estoque e ajusta o estoque do produto.
     *
     * @param int         $tenantId
     * @param int         $productId
     * @param int         $quantity Quantidade movimentada (sempre positiva)
     * @param string      $type     'in' para entrada, 'out' para saída
     * @param string|null $refType  Tipo de referência (order, purchase, manual)
     * @param int|null    $refId    ID da referência
     * @param string|null $description
     */
    public function recordMovement(int $tenantId, int $productId, int $quantity, string $type, ?string $refType = null, ?int $refId = null, ?string $description = null): StockMovementEntity
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Quantidade deve ser maior que zero');
        }
        $product = $this->productRepo->find($productId, $tenantId);
        if (! $product) {
            throw new RuntimeException('Produto não encontrado para este tenant');
        }
        // Calcula novo estoque
        if ($type === 'in') {
            $newQty = $product->stock_quantity + $quantity;
        } elseif ($type === 'out') {
            if ($product->stock_quantity < $quantity) {
                throw new RuntimeException('Estoque insuficiente para saída');
            }
            $newQty = $product->stock_quantity - $quantity;
        } else {
            throw new RuntimeException('Tipo de movimentação inválido');
        }
        // Atualiza estoque
        $this->productRepo->update($productId, $tenantId, ['stock_quantity' => $newQty]);
        // Registra movimentação
        $movementData = [
            'tenant_id'      => $tenantId,
            'product_id'     => $productId,
            'type'           => $type,
            'quantity'       => $quantity,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'description'    => $description,
            'created_at'     => date('Y-m-d H:i:s'),
        ];
        return $this->movementRepo->create($movementData);
    }

    /**
     * Lista movimentações por tenant e opcionalmente por produto.
     *
     * @param int      $tenantId
     * @param int|null $productId
     * @return StockMovementEntity[]
     */
    public function list(int $tenantId, ?int $productId = null): array
    {
        return $this->movementRepo->findByTenant($tenantId, $productId);
    }
}