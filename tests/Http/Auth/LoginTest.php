<?php

use Illuminate\Support\Facades\Auth;

test('login page loads', function () {
    $response = $this->get('/auth/login');

    $response->assertStatus(200);
});

test('successful login with valid credentials', function () {
    \App\Models\User::factory()->create([
        'email' => 'logintest@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/auth/authenticate', [
        'username' => 'logintest@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/');
    expect(Auth::check())->toBeTrue();
});

test('failed login with invalid credentials', function () {
    $response = $this->post('/auth/authenticate', [
        'username' => 'nonexistent@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(200)
        ->assertJson(['errors' => 'Wrong Credentials']);
    expect(Auth::check())->toBeFalse();
});

test('logout functionality works', function () {
    \App\Models\User::factory()->create([
        'email' => 'logouttest@example.com',
        'password' => bcrypt('password123'),
    ]);
    $response = $this->get('/auth/logout');

    $response->assertRedirect('/auth/login');
    expect(Auth::check())->toBeFalse();
});
