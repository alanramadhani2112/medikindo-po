<?php

namespace App\Services;

use App\Models\Supplier;
use DomainException;

class SupplierService
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Validate if supplier can be deactivated
     */
    public function canDeactivate(Supplier $supplier): array
    {
        $issues = [];

        // Check for active products
        $activeProductsCount = $supplier->products()->where('is_active', true)->count();
        if ($activeProductsCount > 0) {
            $issues[] = "Supplier memiliki {$activeProductsCount} produk aktif";
        }

        // Check for pending purchase orders
        $pendingPOsCount = $supplier->purchaseOrders()
            ->whereIn('status', ['draft', 'submitted', 'approved', 'partially_received'])
            ->count();
        if ($pendingPOsCount > 0) {
            $issues[] = "Supplier memiliki {$pendingPOsCount} purchase order yang belum selesai";
        }

        return [
            'can_deactivate' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Safely deactivate supplier with validation
     */
    public function deactivate(Supplier $supplier, int $userId): void
    {
        $validation = $this->canDeactivate($supplier);
        
        if (!$validation['can_deactivate']) {
            throw new DomainException(
                'Tidak dapat menonaktifkan supplier: ' . implode(', ', $validation['issues'])
            );
        }

        $supplier->update(['is_active' => false]);

        $this->auditService->log(
            action: 'supplier.deactivated',
            entityType: Supplier::class,
            entityId: $supplier->id,
            metadata: ['validation_passed' => true],
            userId: $userId,
        );
    }
}