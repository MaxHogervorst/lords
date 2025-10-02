<?php

namespace Tests\Feature;

use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\Member;
use App\Services\FiscusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiscusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FiscusService $fiscusService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fiscusService = new FiscusService;
    }

    public function test_create_invoice_product_with_members(): void
    {
        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
        $members = Member::factory()->count(3)->create();

        $data = [
            'finalproductname' => 'Test Product',
            'finalpriceperperson' => 10.50,
            'finalproductdescription' => 'Test Description',
            'member' => $members->pluck('id')->toArray(),
        ];

        $result = $this->fiscusService->createInvoiceProduct($data, $invoiceGroup->id);

        $this->assertEquals('Test Product', $result['product_name']);
        $this->assertEquals(10.50, $result['price']);
        $this->assertEquals(3, $result['member_count']);

        $this->assertDatabaseHas('invoice_products', [
            'name' => 'Test Product',
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $this->assertDatabaseCount('invoice_lines', 3);
    }

    public function test_update_invoice_product_creates_new_price(): void
    {
        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
        $invoiceProduct = InvoiceProduct::factory()->create([
            'invoice_group_id' => $invoiceGroup->id,
        ]);
        $members = Member::factory()->count(2)->create();

        $data = [
            'finalpriceperperson' => 15.00,
            'finalproductdescription' => 'Updated Description',
            'member' => $members->pluck('id')->toArray(),
        ];

        $result = $this->fiscusService->updateInvoiceProduct($invoiceProduct, $data, null);

        $this->assertEquals('added new price', $result['update_type']);
        $this->assertEquals(15.00, $result['price']);
        $this->assertEquals(2, $result['member_count']);
    }

    public function test_delete_invoice_product_cascades(): void
    {
        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
        $members = Member::factory()->count(2)->create();

        $data = [
            'finalproductname' => 'Product to Delete',
            'finalpriceperperson' => 20.00,
            'finalproductdescription' => 'Will be deleted',
            'member' => $members->pluck('id')->toArray(),
        ];

        $result = $this->fiscusService->createInvoiceProduct($data, $invoiceGroup->id);
        $invoiceProduct = InvoiceProduct::where('name', 'Product to Delete')->first();

        $this->assertDatabaseHas('invoice_products', ['name' => 'Product to Delete']);

        $deletedName = $this->fiscusService->deleteInvoiceProduct($invoiceProduct);

        $this->assertEquals('Product to Delete', $deletedName);
        $this->assertDatabaseMissing('invoice_products', ['name' => 'Product to Delete']);
        $this->assertDatabaseCount('invoice_lines', 0);
    }
}
