<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'organization_id'   => null,
            'is_active'         => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => ['email_verified_at' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => ['is_active' => false]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => ['organization_id' => $organization->id]);
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
            $user->assignRole($role);
        });
    }

    public function healthcareUser(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'Healthcare User', 'guard_name' => 'sanctum']);
            $user->assignRole($role);
        });
    }

    public function finance(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'sanctum']);
            $user->assignRole($role);
        });
    }

    public function financeUser(): static
    {
        return $this->finance();
    }

    public function approver(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'Approver', 'guard_name' => 'sanctum']);
            $user->assignRole($role);
        });
    }

    public function procurementStaff(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'Procurement Staff', 'guard_name' => 'sanctum']);
            $user->assignRole($role);
        });
    }
}
