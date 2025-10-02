<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    // Clear cache
    \Cache::flush();

    // Create required data for tests
    Product::factory()->create();
    InvoiceGroup::factory()->create(['status' => true]);
});

test('create member requires authentication', function () {
    $this->json('POST', '/member', ['name' => 'Sally'])
        ->assertDontSee('Whoops')
        ->assertSee('Unauthorized.');
});

test('create member validates required fields', function () {
    $user = User::factory()->create([
        'email' => 'membertest@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user)
        ->withSession([])
        ->json('POST', '/member', ['name' => 'Sally'])
        ->assertDontSee('Whoops')
        ->assertJsonMissing(['success' => true])
        ->assertJsonStructure(['errors']);
});

test('create member successfully', function () {
    $user = User::factory()->create([
        'email' => 'membertest@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user)
        ->withSession([])
        ->json('POST', '/member', ['name' => 'Sally', 'lastname' => 'Test'])
        ->assertDontSee('Whoops')
        ->assertJson([
            'success' => true,
            'firstname' => 'Sally',
            'lastname' => 'Test',
        ]);

    $this->assertDatabaseHas('members', ['firstname' => 'Sally', 'lastname' => 'Test']);
});
