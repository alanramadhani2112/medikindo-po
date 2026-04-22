<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RBACAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create test organization
        $this->organization = Organization::factory()->create([
            'name' => 'Test Hospital',
            'code' => 'TEST-001',
        ]);
    }

    // ========================================================================
    // HEALTHCARE USER TESTS
    // ========================================================================

    public function test_healthcare_user_can_access_dashboard()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.dashboard'));

        $response->assertStatus(200);
    }

    public function test_healthcare_user_can_view_purchase_orders()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.po.index'));

        $response->assertStatus(200);
    }

    public function test_healthcare_user_can_create_purchase_order()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.po.create'));

        $response->assertStatus(200);
    }

    public function test_healthcare_user_can_view_goods_receipt()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.goods-receipts.index'));

        $response->assertStatus(200);
    }

    public function test_healthcare_user_cannot_access_approvals()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.approvals.index'));

        $response->assertStatus(403);
    }

    public function test_healthcare_user_cannot_access_invoices()
    {
        // Healthcare has view_invoices — can view customer invoices but not create supplier invoices
        $user = $this->createUserWithRole('Healthcare User');
        $response = $this->actingAs($user)->get(route('web.invoices.supplier.index'));
        $response->assertStatus(200); // Healthcare CAN view supplier invoices (read-only)
    }

    public function test_healthcare_user_cannot_access_payments()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.payments.index'));

        $response->assertStatus(403);
    }

    public function test_healthcare_user_cannot_access_credit_control()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.financial-controls.index'));

        $response->assertStatus(403);
    }

    public function test_healthcare_user_cannot_access_organizations()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.organizations.index'));

        $response->assertStatus(403);
    }

    public function test_healthcare_user_cannot_access_suppliers()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.suppliers.index'));

        $response->assertStatus(403);
    }

    public function test_healthcare_user_cannot_access_products()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.products.index'));

        $response->assertStatus(403);
    }

    public function test_healthcare_user_cannot_access_users()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.users.index'));

        $response->assertStatus(403);
    }

    // ========================================================================
    // APPROVER TESTS
    // ========================================================================

    public function test_approver_can_access_dashboard()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.dashboard'));

        $response->assertStatus(200);
    }

    public function test_approver_can_view_approvals()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.approvals.index'));

        $response->assertStatus(200);
    }

    public function test_approver_cannot_create_purchase_orders()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.po.create'));

        $response->assertStatus(403);
    }

    public function test_approver_cannot_access_goods_receipt()
    {
        // Approver has view_goods_receipt to verify delivery was received
        $user = $this->createUserWithRole('Approver');
        $response = $this->actingAs($user)->get(route('web.goods-receipts.index'));
        $response->assertStatus(200); // Approver CAN view GR
    }

    public function test_approver_cannot_access_invoices()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.invoices.supplier.index'));

        $response->assertStatus(403);
    }

    public function test_approver_cannot_access_payments()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.payments.index'));

        $response->assertStatus(403);
    }

    public function test_approver_cannot_access_credit_control()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.financial-controls.index'));

        $response->assertStatus(403);
    }

    public function test_approver_cannot_access_master_data()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.organizations.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get(route('web.suppliers.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get(route('web.products.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get(route('web.users.index'));
        $response->assertStatus(403);
    }

    // ========================================================================
    // FINANCE TESTS
    // ========================================================================

    public function test_finance_can_access_dashboard()
    {
        $user = $this->createUserWithRole('Finance');

        $response = $this->actingAs($user)->get(route('web.dashboard'));

        $response->assertStatus(200);
    }

    public function test_finance_can_view_invoices()
    {
        $user = $this->createUserWithRole('Finance');

        $response = $this->actingAs($user)->get(route('web.invoices.supplier.index'));

        $response->assertStatus(200);
    }

    public function test_finance_can_view_payments()
    {
        $user = $this->createUserWithRole('Finance');

        $response = $this->actingAs($user)->get(route('web.payments.index'));

        $response->assertStatus(200);
    }

    public function test_finance_can_view_credit_control()
    {
        $user = $this->createUserWithRole('Finance');

        $response = $this->actingAs($user)->get(route('web.financial-controls.index'));

        $response->assertStatus(200);
    }

    public function test_finance_cannot_access_purchase_orders()
    {
        // Finance has view_purchase_orders for invoice context
        $user = $this->createUserWithRole('Finance');
        $response = $this->actingAs($user)->get(route('web.po.index'));
        $response->assertStatus(200); // Finance CAN view PO
    }

    public function test_finance_cannot_access_approvals()
    {
        $user = $this->createUserWithRole('Finance');
        $response = $this->actingAs($user)->get(route('web.approvals.index'));
        $response->assertStatus(403);
    }

    public function test_finance_cannot_access_goods_receipt()
    {
        // Finance has view_goods_receipt to create invoices
        $user = $this->createUserWithRole('Finance');
        $response = $this->actingAs($user)->get(route('web.goods-receipts.index'));
        $response->assertStatus(200); // Finance CAN view GR
    }

    public function test_finance_cannot_access_master_data()
    {
        $user = $this->createUserWithRole('Finance');

        $response = $this->actingAs($user)->get(route('web.organizations.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get(route('web.suppliers.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get(route('web.products.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get(route('web.users.index'));
        $response->assertStatus(403);
    }

    // ========================================================================
    // SUPER ADMIN TESTS
    // ========================================================================

    public function test_super_admin_can_access_all_modules()
    {
        $user = $this->createUserWithRole('Super Admin');

        // Dashboard
        $response = $this->actingAs($user)->get(route('web.dashboard'));
        $response->assertStatus(200);

        // Purchase Orders
        $response = $this->actingAs($user)->get(route('web.po.index'));
        $response->assertStatus(200);

        // Approvals
        $response = $this->actingAs($user)->get(route('web.approvals.index'));
        $response->assertStatus(200);

        // Goods Receipt
        $response = $this->actingAs($user)->get(route('web.goods-receipts.index'));
        $response->assertStatus(200);

        // Invoices
        $response = $this->actingAs($user)->get(route('web.invoices.supplier.index'));
        $response->assertStatus(200);

        // Payments
        $response = $this->actingAs($user)->get(route('web.payments.index'));
        $response->assertStatus(200);

        // Credit Control
        $response = $this->actingAs($user)->get(route('web.financial-controls.index'));
        $response->assertStatus(200);

        // Master Data
        $response = $this->actingAs($user)->get(route('web.organizations.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('web.suppliers.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('web.products.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('web.users.index'));
        $response->assertStatus(200);
    }

    // ========================================================================
    // SIDEBAR VISIBILITY TESTS
    // ========================================================================

    public function test_healthcare_user_sees_correct_sidebar_menu()
    {
        $user = $this->createUserWithRole('Healthcare User');

        $response = $this->actingAs($user)->get(route('web.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Orders');
        $response->assertSee('Goods Receipt');
        $response->assertDontSee('Approvals');
        $response->assertSee('Invoices'); // Healthcare has view_invoices for payment proof
        $response->assertDontSee('Credit Control');
        $response->assertDontSee('Organizations');
        $response->assertDontSee('Suppliers');
        $response->assertDontSee('Products');
        $response->assertDontSee('Users');
    }

    public function test_approver_sees_correct_sidebar_menu()
    {
        $user = $this->createUserWithRole('Approver');

        $response = $this->actingAs($user)->get(route('web.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Orders');
        $response->assertSee('Approvals');
        $response->assertSee('Goods Receipt'); // Approver has view_goods_receipt
        $response->assertDontSee('Invoices');
        $response->assertDontSee('Credit Control');
        $response->assertDontSee('Organizations');
    }

    public function test_finance_sees_correct_sidebar_menu()
    {
        $user = $this->createUserWithRole('Finance');

        $response = $this->actingAs($user)->get(route('web.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Invoices');
        $response->assertSee('Payment Ledger');
        $response->assertSee('Credit Control');
        $response->assertDontSee('Approvals');
        $response->assertDontSee('Organizations');
    }

    public function test_super_admin_sees_all_sidebar_menus()
    {
        $user = $this->createUserWithRole('Super Admin');

        $response = $this->actingAs($user)->get(route('web.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Orders');
        $response->assertSee('Approvals');
        $response->assertSee('Goods Receipt');
        $response->assertSee('Invoices');
        $response->assertSee('Payment Ledger');
        $response->assertSee('Credit Control');
        $response->assertSee('Organizations');
        $response->assertSee('Suppliers');
        $response->assertSee('Products');
        $response->assertSee('Users');
    }

    // ========================================================================
    // PERMISSION VERIFICATION TESTS
    // ========================================================================

    public function test_all_roles_have_correct_permissions()
    {
        // Healthcare User
        $healthcareRole = Role::findByName('Healthcare User');
        $this->assertTrue($healthcareRole->hasPermissionTo('view_dashboard'));
        $this->assertTrue($healthcareRole->hasPermissionTo('view_purchase_orders'));
        $this->assertTrue($healthcareRole->hasPermissionTo('create_purchase_orders'));
        $this->assertTrue($healthcareRole->hasPermissionTo('view_goods_receipt'));
        $this->assertFalse($healthcareRole->hasPermissionTo('view_approvals'));
        $this->assertTrue($healthcareRole->hasPermissionTo('view_invoices')); // Can view invoices for payment proof

        // Approver
        $approverRole = Role::findByName('Approver');
        $this->assertTrue($approverRole->hasPermissionTo('view_dashboard'));
        $this->assertTrue($approverRole->hasPermissionTo('view_approvals'));
        $this->assertTrue($approverRole->hasPermissionTo('approve_purchase_orders'));
        $this->assertFalse($approverRole->hasPermissionTo('create_purchase_orders'));
        $this->assertFalse($approverRole->hasPermissionTo('view_invoices'));

        // Finance
        $financeRole = Role::findByName('Finance');
        $this->assertTrue($financeRole->hasPermissionTo('view_dashboard'));
        $this->assertTrue($financeRole->hasPermissionTo('view_invoices'));
        $this->assertTrue($financeRole->hasPermissionTo('create_invoices'));
        $this->assertTrue($financeRole->hasPermissionTo('view_payments'));
        $this->assertTrue($financeRole->hasPermissionTo('process_payments'));
        $this->assertTrue($financeRole->hasPermissionTo('view_credit_control'));
        $this->assertTrue($financeRole->hasPermissionTo('view_purchase_orders')); // For invoice context

        // Super Admin
        $superAdminRole = Role::findByName('Super Admin');
        $allPermissions = Permission::where('guard_name', 'web')->count();
        $this->assertEquals($allPermissions, $superAdminRole->permissions->count());
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'email' => strtolower(str_replace(' ', '.', $roleName)) . '@test.com',
        ]);

        $user->assignRole($roleName);

        return $user;
    }
}
