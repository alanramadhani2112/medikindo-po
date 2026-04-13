<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Organization;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    public function test_super_admin_can_read_audit_logs(): void
    {
        $this->actingAsSuperAdmin();

        AuditLog::create([
            'user_id'     => null,
            'action'      => 'po.created',
            'entity_type' => 'PurchaseOrder',
            'entity_id'   => 1,
            'metadata'    => ['po_number' => 'PO-TEST-0001'],
            'ip_address'  => '127.0.0.1',
        ]);

        $this->getJson('/api/audit-logs')
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_clinic_admin_can_read_audit_logs(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);

        $this->getJson('/api/audit-logs')->assertOk();
    }

    public function test_approver_cannot_read_audit_logs(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsApprover($organization);

        $this->getJson('/api/audit-logs')->assertStatus(403);
    }

    public function test_logs_can_be_filtered_by_action(): void
    {
        $this->actingAsSuperAdmin();

        AuditLog::create([
            'action'      => 'po.created',
            'entity_type' => 'PurchaseOrder',
            'entity_id'   => 1,
        ]);
        AuditLog::create([
            'action'      => 'po.approved',
            'entity_type' => 'PurchaseOrder',
            'entity_id'   => 2,
        ]);

        $response = $this->getJson('/api/audit-logs?action=approved');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
