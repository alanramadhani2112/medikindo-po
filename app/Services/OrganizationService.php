<?php

namespace App\Services;

use App\Models\Organization;
use DomainException;

class OrganizationService
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Validate if organization can be deactivated
     */
    public function canDeactivate(Organization $organization): array
    {
        $issues = [];

        // Check for active users
        $activeUsersCount = $organization->users()->where('is_active', true)->count();
        if ($activeUsersCount > 0) {
            $issues[] = "Organization memiliki {$activeUsersCount} user aktif";
        }

        // Check for pending purchase orders
        $pendingPOsCount = $organization->purchaseOrders()
            ->whereIn('status', ['draft', 'submitted', 'approved', 'partially_received'])
            ->count();
        if ($pendingPOsCount > 0) {
            $issues[] = "Organization memiliki {$pendingPOsCount} purchase order yang belum selesai";
        }

        // Check for outstanding invoices
        $outstandingInvoicesCount = $organization->customerInvoices()
            ->whereIn('status', ['issued', 'partial_paid'])
            ->count();
        if ($outstandingInvoicesCount > 0) {
            $issues[] = "Organization memiliki {$outstandingInvoicesCount} invoice yang belum lunas";
        }

        return [
            'can_deactivate' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Safely deactivate organization with validation
     */
    public function deactivate(Organization $organization, int $userId): void
    {
        $validation = $this->canDeactivate($organization);
        
        if (!$validation['can_deactivate']) {
            throw new DomainException(
                'Tidak dapat menonaktifkan organization: ' . implode(', ', $validation['issues'])
            );
        }

        $organization->update(['is_active' => false]);

        $this->auditService->log(
            action: 'organization.deactivated',
            entityType: Organization::class,
            entityId: $organization->id,
            metadata: ['validation_passed' => true],
            userId: $userId,
        );
    }
}