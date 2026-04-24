<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\CustomerInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // BANK ACCOUNT MODEL TESTS
    // -----------------------------------------------------------------------

    public function test_can_create_bank_account(): void
    {
        $bankAccount = BankAccount::factory()->create([
            'bank_name' => 'Bank BCA',
            'bank_code' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'PT Medikindo',
            'account_type' => 'both',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('bank_accounts', [
            'bank_name' => 'Bank BCA',
            'bank_code' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'PT Medikindo',
            'account_type' => 'both',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->assertEquals('Bank BCA', $bankAccount->bank_name);
        $this->assertTrue($bankAccount->is_active);
        $this->assertTrue($bankAccount->is_default);
    }

    public function test_bank_account_casts(): void
    {
        $bankAccount = BankAccount::factory()->create([
            'is_active' => '1',
            'is_default' => '0',
            'default_for_receive' => '1',
            'default_for_send' => '0',
            'default_priority' => '5',
            'current_balance' => '1000000.50',
        ]);

        $this->assertIsBool($bankAccount->is_active);
        $this->assertIsBool($bankAccount->is_default);
        $this->assertIsBool($bankAccount->default_for_receive);
        $this->assertIsBool($bankAccount->default_for_send);
        $this->assertIsInt($bankAccount->default_priority);
        $this->assertIsString($bankAccount->current_balance); // Decimal cast returns string
        $this->assertEquals('1000000.50', $bankAccount->current_balance);
    }

    // -----------------------------------------------------------------------
    // SCOPE TESTS
    // -----------------------------------------------------------------------

    public function test_scope_active(): void
    {
        BankAccount::factory()->create(['is_active' => true]);
        BankAccount::factory()->create(['is_active' => false]);

        $activeAccounts = BankAccount::active()->get();
        $this->assertCount(1, $activeAccounts);
        $this->assertTrue($activeAccounts->first()->is_active);
    }

    public function test_scope_for_receive(): void
    {
        BankAccount::factory()->create(['account_type' => 'receive', 'is_active' => true]);
        BankAccount::factory()->create(['account_type' => 'both', 'is_active' => true]);
        BankAccount::factory()->create(['account_type' => 'send', 'is_active' => true]);
        BankAccount::factory()->create(['account_type' => 'receive', 'is_active' => false]);

        $receiveAccounts = BankAccount::forReceive()->get();
        $this->assertCount(2, $receiveAccounts);
        
        foreach ($receiveAccounts as $account) {
            $this->assertContains($account->account_type, ['receive', 'both']);
            $this->assertTrue($account->is_active);
        }
    }

    public function test_scope_for_send(): void
    {
        BankAccount::factory()->create(['account_type' => 'send', 'is_active' => true]);
        BankAccount::factory()->create(['account_type' => 'both', 'is_active' => true]);
        BankAccount::factory()->create(['account_type' => 'receive', 'is_active' => true]);
        BankAccount::factory()->create(['account_type' => 'send', 'is_active' => false]);

        $sendAccounts = BankAccount::forSend()->get();
        $this->assertCount(2, $sendAccounts);
        
        foreach ($sendAccounts as $account) {
            $this->assertContains($account->account_type, ['send', 'both']);
            $this->assertTrue($account->is_active);
        }
    }

    public function test_scope_default_receive(): void
    {
        BankAccount::factory()->create([
            'default_for_receive' => true,
            'is_active' => true,
            'default_priority' => 2,
        ]);
        
        BankAccount::factory()->create([
            'default_for_receive' => true,
            'is_active' => true,
            'default_priority' => 1,
        ]);
        
        BankAccount::factory()->create([
            'default_for_receive' => false,
            'is_active' => true,
        ]);

        $defaultReceiveAccounts = BankAccount::defaultReceive()->get();
        $this->assertCount(2, $defaultReceiveAccounts);
        
        // Should be ordered by priority
        $this->assertEquals(1, $defaultReceiveAccounts->first()->default_priority);
        $this->assertEquals(2, $defaultReceiveAccounts->last()->default_priority);
    }

    public function test_scope_default_send(): void
    {
        BankAccount::factory()->create([
            'default_for_send' => true,
            'is_active' => true,
            'default_priority' => 3,
        ]);
        
        BankAccount::factory()->create([
            'default_for_send' => true,
            'is_active' => true,
            'default_priority' => 1,
        ]);
        
        BankAccount::factory()->create([
            'default_for_send' => false,
            'is_active' => true,
        ]);

        $defaultSendAccounts = BankAccount::defaultSend()->get();
        $this->assertCount(2, $defaultSendAccounts);
        
        // Should be ordered by priority
        $this->assertEquals(1, $defaultSendAccounts->first()->default_priority);
        $this->assertEquals(3, $defaultSendAccounts->last()->default_priority);
    }

    // -----------------------------------------------------------------------
    // HELPER METHOD TESTS
    // -----------------------------------------------------------------------

    public function test_is_active_helper(): void
    {
        $activeAccount = BankAccount::factory()->create(['is_active' => true]);
        $inactiveAccount = BankAccount::factory()->create(['is_active' => false]);

        $this->assertTrue($activeAccount->isActive());
        $this->assertFalse($inactiveAccount->isActive());
    }

    public function test_is_default_helper(): void
    {
        $defaultAccount = BankAccount::factory()->create(['is_default' => true]);
        $nonDefaultAccount = BankAccount::factory()->create(['is_default' => false]);

        $this->assertTrue($defaultAccount->isDefault());
        $this->assertFalse($nonDefaultAccount->isDefault());
    }

    public function test_can_receive_helper(): void
    {
        $receiveAccount = BankAccount::factory()->create(['account_type' => 'receive']);
        $sendAccount = BankAccount::factory()->create(['account_type' => 'send']);
        $bothAccount = BankAccount::factory()->create(['account_type' => 'both']);

        $this->assertTrue($receiveAccount->canReceive());
        $this->assertFalse($sendAccount->canReceive());
        $this->assertTrue($bothAccount->canReceive());
    }

    public function test_can_send_helper(): void
    {
        $receiveAccount = BankAccount::factory()->create(['account_type' => 'receive']);
        $sendAccount = BankAccount::factory()->create(['account_type' => 'send']);
        $bothAccount = BankAccount::factory()->create(['account_type' => 'both']);

        $this->assertFalse($receiveAccount->canSend());
        $this->assertTrue($sendAccount->canSend());
        $this->assertTrue($bothAccount->canSend());
    }

    public function test_get_account_type_label(): void
    {
        $receiveAccount = BankAccount::factory()->create(['account_type' => 'receive']);
        $sendAccount = BankAccount::factory()->create(['account_type' => 'send']);
        $bothAccount = BankAccount::factory()->create(['account_type' => 'both']);

        $this->assertEquals('Terima Masuk', $receiveAccount->getAccountTypeLabel());
        $this->assertEquals('Kirim Keluar', $sendAccount->getAccountTypeLabel());
        $this->assertEquals('Masuk & Keluar', $bothAccount->getAccountTypeLabel());
    }

    public function test_get_account_type_badge_color(): void
    {
        $receiveAccount = BankAccount::factory()->create(['account_type' => 'receive']);
        $sendAccount = BankAccount::factory()->create(['account_type' => 'send']);
        $bothAccount = BankAccount::factory()->create(['account_type' => 'both']);

        $this->assertEquals('success', $receiveAccount->getAccountTypeBadgeColor());
        $this->assertEquals('danger', $sendAccount->getAccountTypeBadgeColor());
        $this->assertEquals('primary', $bothAccount->getAccountTypeBadgeColor());
    }

    // -----------------------------------------------------------------------
    // RELATIONSHIP TESTS
    // -----------------------------------------------------------------------

    public function test_bank_account_has_payments_relationship(): void
    {
        $bankAccount = BankAccount::factory()->create();
        
        // Create payments linked to this bank account
        Payment::factory()->count(2)->create(['bank_account_id' => $bankAccount->id]);
        
        $this->assertCount(2, $bankAccount->payments);
        $this->assertInstanceOf(Payment::class, $bankAccount->payments->first());
    }

    public function test_bank_account_has_customer_invoices_relationship(): void
    {
        $bankAccount = BankAccount::factory()->create();
        
        // Create customer invoices linked to this bank account
        CustomerInvoice::factory()->count(3)->create(['bank_account_id' => $bankAccount->id]);
        
        $this->assertCount(3, $bankAccount->customerInvoices);
        $this->assertInstanceOf(CustomerInvoice::class, $bankAccount->customerInvoices->first());
    }

    public function test_incoming_payments_relationship(): void
    {
        $bankAccount = BankAccount::factory()->create();
        
        // Create incoming and outgoing payments
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'incoming',
        ]);
        
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'outgoing',
        ]);

        $this->assertCount(1, $bankAccount->incomingPayments);
        $this->assertEquals('incoming', $bankAccount->incomingPayments->first()->type);
    }

    public function test_outgoing_payments_relationship(): void
    {
        $bankAccount = BankAccount::factory()->create();
        
        // Create incoming and outgoing payments
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'incoming',
        ]);
        
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'outgoing',
        ]);

        $this->assertCount(1, $bankAccount->outgoingPayments);
        $this->assertEquals('outgoing', $bankAccount->outgoingPayments->first()->type);
    }

    // -----------------------------------------------------------------------
    // BUSINESS LOGIC TESTS
    // -----------------------------------------------------------------------

    public function test_can_be_deleted_when_no_dependencies(): void
    {
        $bankAccount = BankAccount::factory()->create();

        $this->assertTrue($bankAccount->canBeDeleted());
    }

    public function test_cannot_be_deleted_with_customer_invoices(): void
    {
        $bankAccount = BankAccount::factory()->create();
        CustomerInvoice::factory()->create(['bank_account_id' => $bankAccount->id]);

        $this->assertFalse($bankAccount->canBeDeleted());
    }

    public function test_cannot_be_deleted_with_payments(): void
    {
        $bankAccount = BankAccount::factory()->create();
        Payment::factory()->create(['bank_account_id' => $bankAccount->id]);

        $this->assertFalse($bankAccount->canBeDeleted());
    }

    // -----------------------------------------------------------------------
    // CASHFLOW CALCULATION TESTS
    // -----------------------------------------------------------------------

    public function test_total_incoming_calculation(): void
    {
        $bankAccount = BankAccount::factory()->create();
        
        // Create completed incoming payments
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'incoming',
            'status' => 'completed',
            'amount' => 100000,
        ]);
        
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'incoming',
            'status' => 'confirmed',
            'amount' => 50000,
        ]);
        
        // Create pending payment (should not be counted)
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'incoming',
            'status' => 'pending',
            'amount' => 25000,
        ]);

        $this->assertEquals(150000, $bankAccount->total_incoming);
    }

    public function test_total_outgoing_calculation(): void
    {
        $bankAccount = BankAccount::factory()->create();
        
        // Create completed outgoing payments
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'outgoing',
            'status' => 'completed',
            'amount' => 75000,
        ]);
        
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'outgoing',
            'status' => 'confirmed',
            'amount' => 25000,
        ]);
        
        // Create pending payment (should not be counted)
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'outgoing',
            'status' => 'pending',
            'amount' => 10000,
        ]);

        $this->assertEquals(100000, $bankAccount->total_outgoing);
    }

    public function test_net_cashflow_calculation(): void
    {
        $bankAccount = BankAccount::factory()->create();
        
        // Incoming: 200,000
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'incoming',
            'status' => 'completed',
            'amount' => 200000,
        ]);
        
        // Outgoing: 75,000
        Payment::factory()->create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'outgoing',
            'status' => 'completed',
            'amount' => 75000,
        ]);

        // Net: 200,000 - 75,000 = 125,000
        $this->assertEquals(125000, $bankAccount->net_cashflow);
    }

    // -----------------------------------------------------------------------
    // ACCOUNT TYPE VALIDATION TESTS
    // -----------------------------------------------------------------------

    public function test_account_type_enum_values(): void
    {
        $receiveAccount = BankAccount::factory()->create(['account_type' => 'receive']);
        $sendAccount = BankAccount::factory()->create(['account_type' => 'send']);
        $bothAccount = BankAccount::factory()->create(['account_type' => 'both']);

        $this->assertEquals('receive', $receiveAccount->account_type);
        $this->assertEquals('send', $sendAccount->account_type);
        $this->assertEquals('both', $bothAccount->account_type);
    }

    public function test_default_priority_ordering(): void
    {
        $account1 = BankAccount::factory()->create([
            'default_for_receive' => true,
            'is_active' => true,
            'default_priority' => 3,
        ]);
        
        $account2 = BankAccount::factory()->create([
            'default_for_receive' => true,
            'is_active' => true,
            'default_priority' => 1,
        ]);
        
        $account3 = BankAccount::factory()->create([
            'default_for_receive' => true,
            'is_active' => true,
            'default_priority' => 2,
        ]);

        $orderedAccounts = BankAccount::defaultReceive()->get();
        
        $this->assertEquals(1, $orderedAccounts[0]->default_priority);
        $this->assertEquals(2, $orderedAccounts[1]->default_priority);
        $this->assertEquals(3, $orderedAccounts[2]->default_priority);
    }
}