<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\CreditLimit;

class OrganizationObserver
{
    /**
     * Handle the Organization "created" event.
     */
    public function created(Organization $organization): void
    {
        // Auto-create credit limit based on organization type
        $defaultLimit = $this->getDefaultCreditLimit($organization->type);
        
        CreditLimit::create([
            'organization_id' => $organization->id,
            'max_limit' => $defaultLimit,
            'is_active' => true,
        ]);
    }

    /**
     * Get default credit limit based on organization type
     */
    private function getDefaultCreditLimit(string $type): float
    {
        $type = strtolower($type);
        
        return match($type) {
            'hospital', 'rs' => 20000000000.00, // 20 Miliar
            'clinic', 'klinik' => 500000000.00,  // 500 Juta
            default => 500000000.00,             // Default: 500 Juta
        };
    }
}
