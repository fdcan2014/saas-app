<?php
namespace Modules\Inventory\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Inventory\Services\StockMovementService;
use Modules\Core\Services\ContextService;

/**
 * Controlador REST para movimentações de estoque.
 *
 * Permite listar e registrar ajustes manuais de estoque.
 */
class StockMovementController extends ResourceController
{
    protected $format = 'json';
    protected StockMovementService $service;

    public function __construct()
    {
        $this->service = new StockMovementService(
            new \Modules\Inventory\Repositories\StockMovementRepository(),
            new \Modules\Product\Repositories\ProductRepository()
        );
    }

    /**
     * Lista movimentações de estoque do tenant atual. Pode filtrar por product_id via query string.
     */
    public function index()
    {
        $tenantId  = ContextService::getTenantId();
        $productId = $this->request->getGet('product_id');
        $productId = $productId !== null ? (int) $productId : null;
        $movements = $this->service->list($tenantId, $productId);
        return $this->respond($movements);
    }

    /**
     * Registra uma nova movimentação manual de estoque.
     *
     * Espera JSON com: product_id, quantity, type ('in' ou 'out'), description.
     */
    public function create()
    {
        $tenantId = ContextService::getTenantId();
        try {
            $data = $this->request->getJSON(true) ?? [];
            $productId   = $data['product_id'] ?? null;
            $quantity    = $data['quantity'] ?? null;
            $type        = $data['type'] ?? null;
            $description = $data['description'] ?? null;
            if (! $productId || ! $quantity || ! $type) {
                return $this->failValidationErrors('product_id, quantity e type são obrigatórios');
            }
            $movement = $this->service->recordMovement(
                $tenantId,
                (int) $productId,
                (int) $quantity,
                $type,
                'manual',
                null,
                $description
            );
            return $this->respondCreated($movement);
        } catch (\Throwable $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }
}