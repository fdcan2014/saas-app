<?php
namespace Modules\Shipping\Services;

use Modules\Shipping\Repositories\ShippingRepository;
use Modules\Shipping\Entities\ShippingEntity;
use Modules\Order\Repositories\OrderRepository;
use RuntimeException;

/**
 * Serviço para lógica de envios.
 */
class ShippingService
{
    protected ShippingRepository $repo;
    protected OrderRepository $orderRepo;

    public function __construct(ShippingRepository $repo, OrderRepository $orderRepo)
    {
        $this->repo      = $repo;
        $this->orderRepo = $orderRepo;
    }

    /**
     * Lista envios do tenant, opcionalmente por pedido.
     *
     * @return ShippingEntity[]
     */
    public function list(int $tenantId, ?int $orderId = null): array
    {
        return $this->repo->findByTenant($tenantId, $orderId);
    }

    /**
     * Recupera um envio específico.
     */
    public function get(int $id, int $tenantId): ?ShippingEntity
    {
        return $this->repo->find($id, $tenantId);
    }

    /**
     * Cria um envio para um pedido.
     *
     * Valida se o pedido pertence ao tenant.
     */
    public function create(int $tenantId, array $data): ShippingEntity
    {
        $orderId = $data['order_id'] ?? null;
        if (! $orderId) {
            throw new RuntimeException('order_id é obrigatório');
        }
        $order = $this->orderRepo->find($orderId, $tenantId);
        if (! $order) {
            throw new RuntimeException('Pedido não encontrado para este tenant');
        }
        $address = $data['address'] ?? null;
        if (! $address) {
            throw new RuntimeException('Endereço de envio é obrigatório');
        }
        $shippingData = [
            'tenant_id'    => $tenantId,
            'order_id'     => $orderId,
            'address'      => $address,
            'carrier'      => $data['carrier'] ?? null,
            'tracking_code'=> $data['tracking_code'] ?? null,
            'status'       => $data['status'] ?? 'pending',
            'shipped_at'   => $data['shipped_at'] ?? null,
            'delivered_at' => $data['delivered_at'] ?? null,
            'created_at'   => date('Y-m-d H:i:s'),
        ];
        return $this->repo->create($shippingData);
    }

    /**
     * Atualiza um envio.
     *
     * Pode alterar carrier, tracking_code, status, shipped_at e delivered_at.
     */
    public function update(int $id, int $tenantId, array $data): ?ShippingEntity
    {
        $updateData = [];
        foreach (['carrier','tracking_code','status','shipped_at','delivered_at'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            throw new RuntimeException('Nada para atualizar');
        }
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        return $this->repo->update($id, $tenantId, $updateData);
    }
}