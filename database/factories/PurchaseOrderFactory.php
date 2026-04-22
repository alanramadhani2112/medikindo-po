<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number'              => 'PO-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'organization_id'        => Organization::factory(),
            'supplier_id'            => Supplier::factory(),
            'created_by'             => User::factory(),
            'status'                 => PurchaseOrder::STATUS_DRAFT,
            'has_narcotics'          => false,
            'requires_extra_approval'=> false,
            'total_amount'           => 0,
            'requested_date'         => now()->addDays(7)->toDateString(),
            'expected_delivery_date' => now()->addDays(14)->toDateString(),
            'notes'                  => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => ['status' => PurchaseOrder::STATUS_DRAFT]);
    }

    public function submitted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'       => PurchaseOrder::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /**
     * underReview() maps to 'submitted' in the new state machine.
     * Kept for backward compatibility with existing tests.
     */
    public function underReview(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'       => PurchaseOrder::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'       => PurchaseOrder::STATUS_APPROVED,
            'submitted_at' => now()->subHours(2),
            'approved_at'  => now(),
        ]);
    }

    public function withNarcotics(): static
    {
        return $this->state(fn(array $attributes) => [
            'has_narcotics'           => true,
            'requires_extra_approval' => true,
        ]);
    }

    public function shipped(): static
    {
        // shipped status removed — maps to approved (delivery outside system)
        return $this->approved();
    }

    public function delivered(): static
    {
        // delivered status removed — maps to completed
        return $this->completed();
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'        => PurchaseOrder::STATUS_COMPLETED,
            'submitted_at'  => now()->subHours(8),
            'approved_at'   => now()->subHours(6),
            'completed_at'  => now(),
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => ['organization_id' => $organization->id]);
    }

    public function forSupplier(Supplier $supplier): static
    {
        return $this->state(fn(array $attributes) => ['supplier_id' => $supplier->id]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'created_by'      => $user->id,
            'organization_id' => $user->organization_id ?? $attributes['organization_id'],
        ]);
    }
}
