<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('migrate:fresh');
});

test('can view login page', function () {
    visit('/auth/login')
        ->assertSee('Login')
        ->screenshot(filename: 'login-page');
});

test('can login with valid credentials', function () {
    // Create a test user
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    visit('/auth/login')
        ->type('username', 'test@example.com')
        ->type('password', 'password123')
        ->click('Login')
        ->assertUrlIs(url('/'))
        ->screenshot(filename: 'logged-in-home');
});

test('shows error with invalid credentials', function () {
    visit('/auth/login')
        ->type('username', 'invalid@example.com')
        ->type('password', 'wrongpassword')
        ->click('Login')
        ->assertSee('Wrond Credentials')
        ->screenshot(filename: 'login-error');
});

test('can logout', function () {
    // Create and login a user
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'logout@example.com',
        'password' => 'password123',
    ]);
    \Sentinel::login($sentinelUser);

    visit('/auth/logout')
        ->assertUrlIs(url('/auth/login'))
        ->screenshot(filename: 'logged-out');
});
