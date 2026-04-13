<?php

namespace Database\Seeders;

use App\Models\Approval;
use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FixMissingApprovals extends Seeder
{
    public function run(): void
    {
        $pos = PurchaseOrder::where('status', PurchaseOrder::STATUS_SUBMITTED)->get();
        $count = 0;

        foreach ($pos as $po) {
            $created = false;

            // Ensure Level 1 exists
            $l1 = $po->approvals()->firstOrCreate(
                ['level' => Approval::LEVEL_STANDARD],
                ['status' => Approval::STATUS_PENDING]
            );
            if ($l1->wasRecentlyCreated) $created = true;

            // Ensure Level 2 exists if narcotics/extra approval required
            if ($po->requires_extra_approval || $po->has_narcotics) {
                // Force sync requires_extra_approval flag if it's false but has_narcotics is true
                if (!$po->requires_extra_approval) {
                    $po->update(['requires_extra_approval' => true]);
                }

                $l2 = $po->approvals()->firstOrCreate(
                    ['level' => Approval::LEVEL_NARCOTICS],
                    ['status' => Approval::STATUS_PENDING]
                );
                if ($l2->wasRecentlyCreated) $created = true;
            }

            if ($created) {
                $count++;
            }
        }

        $this->command->info("✅ Fixed $count Purchase Orders with missing approval records.");
    }
}
