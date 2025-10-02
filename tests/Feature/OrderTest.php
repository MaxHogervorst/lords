<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        // Clear cache before each test
        \Cache::flush();

        // Create an active invoice group for tests that need it
        factory(\App\Models\InvoiceGroup::class)->create(['status' => true]);

        // Create at least one product for tests
        factory(\App\Models\Product::class)->create();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_create_order()
    {
        $invoiceGroup = \App\Models\InvoiceGroup::where('status', true)->first();
        $product = \App\Models\Product::first();
        $name = 'Sally '.date('d-m-Y');
        $group = factory(\App\Models\Group::class)->create([
            'name' => $name,
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $member = factory(\App\Models\Member::class)->create([
            'firstname' => 'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('POST', '/order/store/member', ['name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $user = \App\Models\User::factory()->create([
            'email' => 'ordertest@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/order/store/member', [
                'memberId' => $group->id,
                'product' => $product->id,
            ])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/order/store/member', [
                'memberId' => $group->id,
                'product' => $product->id,
                'amount' => 2436,
            ])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
                'member_id' => $group->id,
                'product_id' => $product->id,
                'amount' => 2436,
            ]);

        $this->assertDatabaseHas('orders', ['product_id' => $product->id, 'amount' => 2436, 'ownerable_id' => $group->id]);
    }
}
