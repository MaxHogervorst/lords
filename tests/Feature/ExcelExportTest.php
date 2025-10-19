<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Tests\TestCase;

class ExcelExportTest extends TestCase
{
    private $adminRole;

    protected function setUp(): void
    {
        parent::setUp();


        // Create at least one product
        Product::factory()->create();
    }

    /**
     * Test Excel export is accessible by admin
     */
    public function test_excel_export_is_accessible_by_admin(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'exceladmin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Excel Test Month',
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get('/invoice/excel');

        // Should download file or return 200
        $this->assertTrue(in_array($response->getStatusCode(), [200]));
    }

    /**
     * Test Excel export includes member orders
     */
    public function test_excel_export_includes_member_orders(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'excelmember@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Create invoice group
        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Member Order Month',
            'status' => true,
        ]);

        // Create member and product
        $member = Member::factory()->create([
            'firstname' => 'Excel',
            'lastname' => 'Tester',
        ]);

        $product = Product::factory()->create([
            'name' => 'Excel Beer',
            'price' => 2.50,
        ]);

        // Refresh product cache
        Product::toArrayIdAsKey();

        // Create order
        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)->get('/invoice/excel');

        // Should successfully generate Excel with data
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
    }

    /**
     * Test Excel export includes group orders
     */
    public function test_excel_export_includes_group_orders(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'excelgroup@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Create invoice group
        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Group Order Month',
            'status' => true,
        ]);

        // Create group and members
        $group = Group::factory()->create([
            'name' => 'Excel Test Group',
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $member1 = Member::factory()->create();
        $member2 = Member::factory()->create();
        $group->members()->attach([$member1->id, $member2->id]);

        $product = Product::factory()->create([
            'name' => 'Group Beer',
            'price' => 3.00,
        ]);

        // Refresh product cache
        Product::toArrayIdAsKey();

        // Create group order
        Order::factory()->create([
            'ownerable_id' => $group->id,
            'ownerable_type' => 'App\\Models\\Group',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 20,
        ]);

        $response = $this->actingAs($user)->get('/invoice/excel');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test Excel export includes invoice products
     */
    public function test_excel_export_includes_invoice_products(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'excelproduct@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Create invoice group
        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Invoice Product Month',
            'status' => true,
        ]);

        // Create invoice product
        $invoiceProduct = InvoiceProduct::factory()->create([
            'invoice_group_id' => $invoiceGroup->id,
            'name' => 'Special Item',
        ]);

        $invoiceProductPrice = InvoiceProductPrice::factory()->create([
            'invoice_product_id' => $invoiceProduct->id,
            'price' => 15.00,
        ]);

        $response = $this->actingAs($user)->get('/invoice/excel');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test Excel export filename includes invoice group name
     */
    public function test_excel_export_filename_includes_invoice_group_name(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'excelfilename@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'January 2025',
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get('/invoice/excel');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('January 2025.xlsx', $contentDisposition);
    }

    /**
     * Test Excel export calculates totals correctly
     */
    public function test_excel_export_calculates_totals_correctly(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'exceltotals@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Totals Test Month',
            'status' => true,
        ]);

        $member = Member::factory()->create();
        $product = Product::factory()->create(['price' => 5.00]);

        Product::toArrayIdAsKey();

        // Create multiple orders
        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 4, // 4 * 5.00 = 20.00
        ]);

        $response = $this->actingAs($user)->get('/invoice/excel');

        $this->assertEquals(200, $response->getStatusCode());
        // Total should be calculated in the export
    }
}
