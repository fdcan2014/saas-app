<?php
namespace Modules\PurchasePayment\Services;

use Modules\PurchasePayment\Repositories\PurchasePaymentRepository;
use Modules\PurchasePayment\Entities\PurchasePaymentEntity;
use Modules\Purchase\Repositories\PurchaseRepository;
use RuntimeException;

/**
 * Serviço para regras de negócio de pagamentos de compras.
 */
class PurchasePaymentService
{
    protected PurchasePaymentRepository $paymentRepo;
    protected PurchaseRepository $purchaseRepo;

    public function __construct(PurchasePaymentRepository $paymentRepo, PurchaseRepository $purchaseRepo)
    {
        $this->paymentRepo = $paymentRepo;
        $this->purchaseRepo = $purchaseRepo;
    }

    /**
     * Lista pagamentos do tenant e opcionalmente de uma compra específica.
     *
     * @return PurchasePaymentEntity[]
     */
    public function list(int $tenantId, ?int $purchaseId = null): array
    {
        return $this->paymentRepo->findByTenant($tenantId, $purchaseId);
    }

    /**
     * Cria um pagamento para uma compra.
     */
    public function create(int $tenantId, array $data): PurchasePaymentEntity
    {
        $purchaseId = $data['purchase_id'] ?? null;
        if (! $purchaseId) {
            throw new RuntimeException('purchase_id é obrigatório');
        }
        $purchase = $this->purchaseRepo->find($purchaseId, $tenantId);
        if (! $purchase) {
            throw new RuntimeException('Compra não encontrada para este tenant');
        }
        $amount = isset($data['amount']) ? (float) $data['amount'] : 0.0;
        if ($amount <= 0) {
            throw new RuntimeException('Valor do pagamento deve ser maior que zero');
        }
        $paymentData = [
            'tenant_id'   => $tenantId,
            'purchase_id' => $purchaseId,
            'amount'      => $amount,
            'method'      => $data['method'] ?? null,
            'status'      => $data['status'] ?? 'pending',
            'paid_at'     => $data['paid_at'] ?? null,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        $payment = $this->paymentRepo->create($paymentData);
        // Se pagamento estiver marcado como pago, recalcula status da compra
        if ($payment->status === 'paid') {
            $this->recalculatePurchaseStatus($tenantId, $purchaseId);
        }
        return $payment;
    }

    /**
     * Atualiza um pagamento existente.
     */
    public function update(int $id, int $tenantId, array $data): ?PurchasePaymentEntity
    {
        $updateData = [];
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        if (isset($data['paid_at'])) {
            $updateData['paid_at'] = $data['paid_at'];
        }
        if (empty($updateData)) {
            throw new RuntimeException('Nada para atualizar');
        }
        $payment = $this->paymentRepo->update($id, $tenantId, $updateData);
        if ($payment && isset($updateData['status']) && $updateData['status'] === 'paid') {
            $this->recalculatePurchaseStatus($tenantId, $payment->purchase_id);
        }
        return $payment;
    }

    /**
     * Recalcula o status de uma compra de acordo com os pagamentos efetuados.
     *
     * Se a soma dos pagamentos pagos for maior ou igual ao total da compra, status 'paid';
     * se for maior que zero e menor, status 'partial'; caso contrário 'pending'.
     */
    public function recalculatePurchaseStatus(int $tenantId, int $purchaseId): void
    {
        $purchase = $this->purchaseRepo->find($purchaseId, $tenantId);
        if (! $purchase) {
            return;
        }
        $totalPaid = $this->paymentRepo->sumPaidAmount($tenantId, $purchaseId);
        $purchaseTotal = $purchase->total;
        $newStatus = $purchase->status;
        if ($purchaseTotal > 0 && $totalPaid >= $purchaseTotal) {
            $newStatus = 'paid';
        } elseif ($totalPaid > 0 && $totalPaid < $purchaseTotal) {
            $newStatus = 'partial';
        } elseif ($totalPaid == 0) {
            $newStatus = 'pending';
        }
        if ($newStatus !== $purchase->status) {
            $this->purchaseRepo->update($purchaseId, $tenantId, [
                'status'     => $newStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}