<?php

use App\Models\User;

test('login page loads', function () {
    $response = $this->get('/auth/login');

    $response->assertStatus(200);
});

test('successful login with valid credentials', function () {
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'logintest@example.com',
        'password' => 'password123',
    ]);

    $response = $this->post('/auth/authenticate', [
        'username' => 'logintest@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/');
    expect(\Sentinel::check())->not->toBeNull();
});

test('failed login with invalid credentials', function () {
    $response = $this->post('/auth/authenticate', [
        'username' => 'nonexistent@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(200)
        ->assertJson(['errors' => 'Wrond Credentials']);
    expect(\Sentinel::check())->toBeFalse();
});

test('logout functionality works', function () {
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'logouttest@example.com',
        'password' => 'password123',
    ]);
    \Sentinel::login($sentinelUser);

    $response = $this->get('/auth/logout');

    $response->assertRedirect('/auth/login');
    expect(\Sentinel::check())->toBeFalse();
});
