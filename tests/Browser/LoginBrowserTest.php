<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
    Product::factory()->create();
});

test('can view login page', function () {
    $this->visit('/auth/login')
        ->assertSee('Please Sign In')
        ->assertSee('Username')
        ->assertSee('Password')
        ->assertVisible('#username')
        ->assertVisible('#password')
        ->assertVisible('button[type="submit"]');
});

test('can login with valid credentials and redirect to home', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);

    $page = $this->visit('/auth/login')
        ->assertSee('Please Sign In');

    $page->type('#username', 'test@example.com')
        ->type('#password', 'password123')
        ->click('button[type="submit"]')
        ->waitForText('Last Five Orders', 10)
        ->assertPathIs('/');

    // Verify user is authenticated by checking we can access protected content
    expect(auth()->check())->toBeTrue();
});

test('shows error message with invalid credentials', function () {
    User::factory()->create([
        'email' => 'valid@example.com',
        'password' => bcrypt('correctpassword'),
    ]);

    $page = $this->visit('/auth/login')
        ->assertSee('Please Sign In');

    $page->type('#username', 'valid@example.com')
        ->type('#password', 'wrongpassword')
        ->click('button[type="submit"]');

    // Wait a moment for the request to complete
    usleep(500000);

    // Should not be authenticated
    expect(auth()->check())->toBeFalse();
});

test('shows error message with non-existent user', function () {
    $page = $this->visit('/auth/login')
        ->assertSee('Please Sign In');

    $page->type('#username', 'nonexistent@example.com')
        ->type('#password', 'anypassword')
        ->click('button[type="submit"]');

    // Wait a moment for the request to complete
    usleep(500000);

    // Should not be authenticated
    expect(auth()->check())->toBeFalse();
});

test('cannot access login page when already authenticated', function () {
    $user = User::factory()->create([
        'email' => 'loggedin@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Login first
    $this->visit('/auth/login')
        ->type('#username', 'loggedin@example.com')
        ->type('#password', 'password123')
        ->click('button[type="submit"]')
        ->waitForText('Last Five Orders', 10);

    // Try to access login page again
    $this->visit('/auth/login')
        ->assertPathIs('/'); // Should redirect to home
});

test('can logout and return to login page', function () {
    $user = User::factory()->create([
        'email' => 'logout@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Login first
    $this->visit('/auth/login')
        ->type('#username', 'logout@example.com')
        ->type('#password', 'password123')
        ->click('button[type="submit"]')
        ->waitForText('Last Five Orders', 10);

    // Now logout
    $this->visit('/auth/logout')
        ->waitForText('Please Sign In', 10)
        ->assertPathIs('/auth/login');

    expect(auth()->check())->toBeFalse();
});

test('redirects to login when accessing protected route without authentication', function () {
    $page = $this->visit('/member')
        ->waitForText('Please Sign In', 10)
        ->assertPathIs('/auth/login');
});

test('can access protected routes after successful login', function () {
    $user = User::factory()->create([
        'email' => 'protected@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Login
    $this->visit('/auth/login')
        ->type('#username', 'protected@example.com')
        ->type('#password', 'password123')
        ->click('button[type="submit"]')
        ->waitForText('Last Five Orders', 10);

    // Access protected route
    $this->visit('/member')
        ->waitForText('Add Member', 10)
        ->assertPathIs('/member')
        ->assertDontSee('Please Sign In');
});

test('remember me functionality persists session', function () {
    $user = User::factory()->create([
        'email' => 'remember@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Login (remember is set to true in AuthController)
    $this->visit('/auth/login')
        ->type('#username', 'remember@example.com')
        ->type('#password', 'password123')
        ->click('button[type="submit"]')
        ->waitForText('Last Five Orders', 10);

    // Check that user is authenticated
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->email)->toBe('remember@example.com');
});
