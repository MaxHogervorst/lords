<?php

namespace Tests\Feature;

use App\Models\InvoiceGroup;
use App\Models\Product;
use Sentinel;
use Tests\TestCase;

class LinkCheckTest extends TestCase
{

    private $adminUser;

    private $regularUser;

    private $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache and logout any existing session
        \Cache::flush();
        if (Sentinel::check()) {
            Sentinel::logout();
        }

        // Create required data for tests
        Product::factory()->create();
        InvoiceGroup::factory()->create(['status' => true]);

        // Create admin role
        $this->adminRole = Sentinel::getRoleRepository()->createModel()->firstOrCreate([
            'slug' => 'admin',
        ], [
            'name' => 'Admin',
        ]);

        // Create admin user (user ID 3 equivalent)
        $this->adminUser = Sentinel::registerAndActivate([
            'email' => 'admin@linktest.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($this->adminUser);

        // Create regular user (user ID 1 equivalent)
        $this->regularUser = Sentinel::registerAndActivate([
            'email' => 'regular@linktest.com',
            'password' => 'password',
        ]);
    }

    protected function tearDown(): void
    {
        // Logout after each test
        if (Sentinel::check()) {
            Sentinel::logout();
        }

        parent::tearDown();
    }

    public function test_home()
    {
        $response = $this->get('/');
        $this->assertEquals(302, $response->getStatusCode());

        Sentinel::login($this->adminUser);
        $user = \App\Models\User::find($this->adminUser->id);

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

        Sentinel::login($this->adminUser);
        $user = \App\Models\User::find($this->adminUser->id);

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

        Sentinel::login($this->adminUser);
        $user = \App\Models\User::find($this->adminUser->id);

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

        Sentinel::login($this->adminUser);
        $user = \App\Models\User::find($this->adminUser->id);

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

        Sentinel::login($this->adminUser);
        $user = \App\Models\User::find($this->adminUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->get('/fiscus')
            ->assertStatus(200)
            ->assertDontSee('Whoops');

        Sentinel::logout();

        Sentinel::login($this->regularUser);
        $regularUser = \App\Models\User::find($this->regularUser->id);

        $response = $this->actingAs($regularUser)
            ->withSession([])
            ->get('/fiscus');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_invoice()
    {
        $response = $this->get('/invoice');
        $this->assertEquals(302, $response->getStatusCode());

        Sentinel::login($this->adminUser);
        $user = \App\Models\User::find($this->adminUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->get('/invoice')
            ->assertStatus(200)
            ->assertDontSee('Whoops');

        Sentinel::logout();

        Sentinel::login($this->regularUser);
        $regularUser = \App\Models\User::find($this->regularUser->id);

        $response = $this->actingAs($regularUser)
            ->withSession([])
            ->get('/invoice');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_sepa()
    {
        $response = $this->get('/sepa');
        $this->assertEquals(302, $response->getStatusCode());

        Sentinel::login($this->adminUser);
        $user = \App\Models\User::find($this->adminUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->get('/sepa')
            ->assertStatus(200)
            ->assertDontSee('Whoops');

        Sentinel::logout();

        Sentinel::login($this->regularUser);
        $regularUser = \App\Models\User::find($this->regularUser->id);

        $response = $this->actingAs($regularUser)
            ->withSession([])
            ->get('/sepa');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
