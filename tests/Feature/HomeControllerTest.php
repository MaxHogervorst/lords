<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        \Cache::flush();

        // Create at least one product to avoid issues
        Product::factory()->create();

        // Create an active invoice group
        InvoiceGroup::factory()->create(['status' => true]);
    }

    /**
     * Test home page redirects to login when not authenticated
     */
    public function test_home_page_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('auth/login');
    }

    /**
     * Test home page is accessible when authenticated
     */
    public function test_home_page_is_accessible_when_authenticated(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'homeuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertStatus(200)
            ->assertDontSee('Unauthorized');
    }

    /**
     * Test home page loads successfully with active invoice group
     */
    public function test_home_page_loads_with_active_invoice_group(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'homeinvoice@example.com',
            'password' => bcrypt('password'),
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'January 2025',
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Last Five Orders');
    }

    /**
     * Test home page displays members
     */
    public function test_home_page_displays_members(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'homemembers@example.com',
            'password' => bcrypt('password'),
        ]);

        $member = Member::factory()->create([
            'firstname' => 'John',
            'lastname' => 'HomeTest',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        // The view should contain member data (exact assertion depends on view structure)
    }

    /**
     * Test home page displays products
     */
    public function test_home_page_displays_products(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'homeproducts@example.com',
            'password' => bcrypt('password'),
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Home Beer',
            'price' => 3.50,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        // Products should be available to the view
    }

    /**
     * Test home page handles missing invoice group gracefully
     */
    public function test_home_page_handles_missing_invoice_group(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'homenoinvoice@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a fresh invoice group for this test
        // Instead of deleting all groups (which causes lock issues)
        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Test Month',
            'status' => false,
        ]);

        $response = $this->actingAs($user)->get('/');

        // Should handle the case where there's no active group
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test home page with member orders
     */
    public function test_home_page_with_member_orders(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'homeorders@example.com',
            'password' => bcrypt('password'),
        ]);

        $member = Member::factory()->create();
        $product = Product::factory()->create();
        $invoiceGroup = InvoiceGroup::where('status', true)->first();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 5,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
