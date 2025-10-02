<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    // Clear cache and logout any existing session
    \Cache::flush();
    if (\Sentinel::check()) {
        \Sentinel::logout();
    }

    // Create required data for tests
    Product::factory()->create();
    InvoiceGroup::factory()->create(['status' => true]);
});

afterEach(function () {
    // Logout after each test
    if (\Sentinel::check()) {
        \Sentinel::logout();
    }
});

test('create member requires authentication', function () {
    $this->json('POST', '/member', ['name' => 'Sally'])
        ->assertDontSee('Whoops')
        ->assertSee('Unauthorized.');
});

test('create member validates required fields', function () {
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'membertest@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($sentinelUser);
    $user = User::find($sentinelUser->id);

    $this->actingAs($user)
        ->withSession([])
        ->json('POST', '/member', ['name' => 'Sally'])
        ->assertDontSee('Whoops')
        ->assertJsonMissing(['success' => true])
        ->assertJsonStructure(['errors']);
});

test('create member successfully', function () {
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'membertest@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($sentinelUser);
    $user = User::find($sentinelUser->id);

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
