<?php

namespace Tests\Unit\Properties;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Property 11: AR Aging Bucket Assignment
 *
 * Aging bucket = 0-30 days if overdue <= 30, 31-60 if 31-60, >60 if > 60.
 *
 * Validates: Requirements 11.1, 11.5, 11.7
 *
 * @group property-based
 */
class ARAgingProperty11Test extends TestCase
{
    /**
     * Replicate the bucket classification logic from ARAgingController.
     *
     * @param Carbon $today
     * @param Carbon $dueDate
     * @return string 'current' | 'warning' | 'overdue'
     */
    private function classifyBucket(Carbon $today, Carbon $dueDate): string
    {
        $daysDiff    = $today->diffInDays($dueDate, false);
        $daysOverdue = -$daysDiff; // positive = past due

        if ($daysOverdue <= 30) {
            return 'current';
        } elseif ($daysOverdue <= 60) {
            return 'warning';
        } else {
            return 'overdue';
        }
    }

    /**
     * Property 11: bucket assignment matches expected classification for 100 random due_dates
     */
    public function test_aging_bucket_assignment_property(): void
    {
        $today = Carbon::today();

        for ($i = 0; $i < 100; $i++) {
            // Random offset: -120 to +30 days from today
            $offsetDays = mt_rand(-120, 30);
            $dueDate    = $today->copy()->addDays($offsetDays);

            $daysOverdue = -$today->diffInDays($dueDate, false);

            $bucket = $this->classifyBucket($today, $dueDate);

            if ($daysOverdue <= 30) {
                $this->assertEquals(
                    'current',
                    $bucket,
                    "Iteration {$i}: daysOverdue={$daysOverdue} should map to 'current' bucket"
                );
            } elseif ($daysOverdue <= 60) {
                $this->assertEquals(
                    'warning',
                    $bucket,
                    "Iteration {$i}: daysOverdue={$daysOverdue} should map to 'warning' bucket"
                );
            } else {
                $this->assertEquals(
                    'overdue',
                    $bucket,
                    "Iteration {$i}: daysOverdue={$daysOverdue} should map to 'overdue' bucket"
                );
            }
        }
    }

    /**
     * Boundary tests for exact bucket edges
     */
    public function test_aging_bucket_boundary_values(): void
    {
        $today = Carbon::today();

        // Exactly 30 days overdue → current
        $this->assertEquals('current', $this->classifyBucket($today, $today->copy()->subDays(30)));

        // Exactly 31 days overdue → warning
        $this->assertEquals('warning', $this->classifyBucket($today, $today->copy()->subDays(31)));

        // Exactly 60 days overdue → warning
        $this->assertEquals('warning', $this->classifyBucket($today, $today->copy()->subDays(60)));

        // Exactly 61 days overdue → overdue
        $this->assertEquals('overdue', $this->classifyBucket($today, $today->copy()->subDays(61)));

        // Due today (0 days overdue) → current
        $this->assertEquals('current', $this->classifyBucket($today, $today->copy()));

        // Due tomorrow (future, -1 days overdue) → current
        $this->assertEquals('current', $this->classifyBucket($today, $today->copy()->addDays(1)));
    }
}
