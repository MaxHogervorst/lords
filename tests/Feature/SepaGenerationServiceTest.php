<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Services\InvoiceCalculationService;
use App\Services\SepaGenerationService;
use Tests\TestCase;

class SepaGenerationServiceTest extends TestCase
{
    private SepaGenerationService $sepaService;
    private InvoiceCalculationService $calculationService;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure SEPA settings with low transaction limit to trigger failures
        \Settings::set('creditorMaxMoneyPerBatch', 999999);
        \Settings::set('creditorMaxTransactionsPerBatch', 1000);
        \Settings::set('creditorMaxMoneyPerTransaction', 50.00); // Low limit to test failures
        \Settings::set('creditorName', 'Test Creditor');
        \Settings::set('creditorAccountIBAN', 'NL91ABNA0417164300');
        \Settings::set('creditorAgentBIC', 'ABNANL2A');
        \Settings::set('creditorId', 'NL00ZZZ000000000000');
        \Settings::set('creditorPain', 'pain.008.001.02');
        \Settings::set('ReqdColltnDt', 5);
        \Settings::set('mandateSignDate', '2014-01-01');
        \Settings::set('remittancePrefix', 'Contributie');
        \Settings::set('filePrefix', 'TEST');
        \Settings::save();

        $this->calculationService = app(InvoiceCalculationService::class);
        $this->sepaService = app(SepaGenerationService::class);
    }

    /**
     * Test that createBatches returns Member objects in failedMembers.
     * This ensures the Blade template can access ->firstname and ->lastname directly.
     */
    public function test_createBatches_returns_failed_members_as_member_objects(): void
    {
        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        // Create a member with an order that exceeds the transaction limit
        $member = Member::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        // Create an order with high amount (100.00 > 50.00 limit)
        $product = Product::factory()->create(['price' => 100.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        // Get member info as generateMemberInfo does
        $member->load([
            'orders' => fn($q) => $q->where('invoice_group_id', $invoiceGroup->id),
            'orders.product',
            'groups' => fn($q) => $q->where('invoice_group_id', $invoiceGroup->id),
            'groups.orders',
            'groups.orders.product',
            'groups.members',
            'invoice_lines.productprice.product'
        ]);

        $memberInfo = $this->calculationService->generateMemberInfo($member, $invoiceGroup);

        // Verify memberInfo is an array with Member object inside
        $this->assertIsArray($memberInfo, 'generateMemberInfo should return an array');
        $this->assertArrayHasKey('m', $memberInfo);
        $this->assertInstanceOf(Member::class, $memberInfo['m']);

        // Create batches with this member
        $result = $this->sepaService->createBatches([
            'RCUR' => [$memberInfo],
            'FRST' => []
        ]);

        // Assert structure
        $this->assertArrayHasKey('batches', $result);
        $this->assertArrayHasKey('failedMembers', $result);

        // The critical assertion: failedMembers should contain the member (because amount > limit)
        $this->assertCount(1, $result['failedMembers'], 'Should have 1 failed member due to exceeding transaction limit');

        $failedMember = $result['failedMembers'][0];

        // FIXED: failedMember should now be a Member object, not an array
        $this->assertInstanceOf(Member::class, $failedMember, 'Failed member should be a Member object');

        // Now the Blade template can access properties directly:
        $this->assertEquals('John', $failedMember->firstname);
        $this->assertEquals('Doe', $failedMember->lastname);
        $this->assertEquals('NL20INGB0001234567', $failedMember->iban);
        $this->assertEquals('INGBNL2A', $failedMember->bic);
    }

    /**
     * Test that members with transactions under the limit are processed successfully.
     */
    public function test_createBatches_processes_valid_members_successfully(): void
    {
        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        // Create an order within the limit (10.00 < 50.00 limit)
        $product = Product::factory()->create(['price' => 10.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $member->load([
            'orders' => fn($q) => $q->where('invoice_group_id', $invoiceGroup->id),
            'orders.product',
            'groups' => fn($q) => $q->where('invoice_group_id', $invoiceGroup->id),
            'groups.orders',
            'groups.orders.product',
            'groups.members',
            'invoice_lines.productprice.product'
        ]);

        $memberInfo = $this->calculationService->generateMemberInfo($member, $invoiceGroup);

        $result = $this->sepaService->createBatches([
            'RCUR' => [$memberInfo],
            'FRST' => []
        ]);

        // Member should be processed successfully (not in failedMembers)
        $this->assertCount(0, $result['failedMembers'], 'Should have no failed members');
        $this->assertCount(1, $result['batches']['RCUR'], 'Should have 1 batch created');
    }
}
