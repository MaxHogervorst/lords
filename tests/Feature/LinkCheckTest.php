<?php

namespace Tests\Feature;

use App\Models\InvoiceGroup;
use App\Models\Product;
use Tests\TestCase;

class LinkCheckTest extends TestCase
{

    private $adminUser;

    private $regularUser;

    private $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache
        \Cache::flush();

        // Create required data for tests
        Product::factory()->create();
        InvoiceGroup::factory()->create(['status' => true]);

        // Create admin user
        $this->adminUser = \App\Models\User::factory()->create([
            'email' => 'admin@linktest.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Create regular user
        $this->regularUser = \App\Models\User::factory()->create([
            'email' => 'regular@linktest.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);
    }

    public function test_home()
    {
        $response = $this->get('/');
        $this->assertEquals(302, $response->getStatusCode());

        $user = $this->adminUser;

        $this->actingAs($user)
            ->withSession([])
            ->get('/')
            ->assertStatus(200)
            ->assertDontSee('Whoops');
    }

    public function test_members()
    {
        $response = $this->get('/member');
        $this->assertEquals(302, $response->getStatusCode());

        $user = $this->adminUser;

        $this->actingAs($user)
            ->withSession([])
            ->get('/member')
            ->assertStatus(200)
            ->assertDontSee('Whoops');
    }

    public function test_groups()
    {
        $response = $this->get('/group');
        $this->assertEquals(302, $response->getStatusCode());

        $user = $this->adminUser;

        $this->actingAs($user)
            ->withSession([])
            ->get('/group')
            ->assertStatus(200)
            ->assertDontSee('Whoops');
    }

    public function test_products()
    {
        $response = $this->get('/product');
        $this->assertEquals(302, $response->getStatusCode());

        $user = $this->adminUser;

        $this->actingAs($user)
            ->withSession([])
            ->get('/product')
            ->assertStatus(200)
            ->assertDontSee('Whoops');
    }

    public function test_fiscus()
    {
        $response = $this->get('/fiscus');
        $this->assertEquals(302, $response->getStatusCode());

        $user = $this->adminUser;

        $this->actingAs($user)
            ->withSession([])
            ->get('/fiscus')
            ->assertStatus(200)
            ->assertDontSee('Whoops');

        $regularUser = $this->regularUser;

        $response = $this->actingAs($regularUser)
            ->withSession([])
            ->get('/fiscus');
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_invoice()
    {
        $response = $this->get('/invoice');
        $this->assertEquals(302, $response->getStatusCode());

        $user = $this->adminUser;

        $this->actingAs($user)
            ->withSession([])
            ->get('/invoice')
            ->assertStatus(200)
            ->assertDontSee('Whoops');

        $regularUser = $this->regularUser;

        $response = $this->actingAs($regularUser)
            ->withSession([])
            ->get('/invoice');
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_sepa()
    {
        $response = $this->get('/sepa');
        $this->assertEquals(302, $response->getStatusCode());

        $user = $this->adminUser;

        $this->actingAs($user)
            ->withSession([])
            ->get('/sepa')
            ->assertStatus(200)
            ->assertDontSee('Whoops');

        $regularUser = $this->regularUser;

        $response = $this->actingAs($regularUser)
            ->withSession([])
            ->get('/sepa');
        $this->assertEquals(403, $response->getStatusCode());
    }
}
