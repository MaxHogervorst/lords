<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthControllerTest extends TestCase
{

    /**
     * Test that login page is accessible
     */
    public function test_login_page_is_accessible()
    {
        $this->get('/auth/login')
            ->assertStatus(200)
            ->assertSee('Login')
            ->assertDontSee('Whoops');
    }

    /**
     * Test successful authentication
     */
    public function test_successful_authentication()
    {
        // Create a test user
        $user = \App\Models\User::factory()->create([
            'email' => 'testauth@example.com',
            'password' => bcrypt('testpassword123'),
            'first_name' => 'Auth',
            'last_name' => 'Test',
        ]);

        // Attempt to authenticate
        $response = $this->call('POST', '/auth/authenticate', [
            'username' => 'testauth@example.com',
            'password' => 'testpassword123',
        ]);

        // Check response - should redirect to home
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Test failed authentication with wrong password
     */
    public function test_failed_authentication_wrong_password()
    {
        // Create a test user
        $user = \App\Models\User::factory()->create([
            'email' => 'testfail@example.com',
            'password' => bcrypt('correctpassword'),
            'first_name' => 'Fail',
            'last_name' => 'Test',
        ]);

        // Attempt to authenticate with wrong password
        $response = $this->call('POST', '/auth/authenticate', [
            'username' => 'testfail@example.com',
            'password' => 'wrongpassword',
        ]);

        // Check response - should return JSON error
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test failed authentication with non-existent user
     */
    public function test_failed_authentication_non_existent_user()
    {
        $response = $this->call('POST', '/auth/authenticate', [
            'username' => 'nonexistent@example.com',
            'password' => 'anypassword',
        ]);

        // Check response - should return JSON error
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test logout functionality
     */
    public function test_logout()
    {
        // Create and login a test user
        $user = \App\Models\User::factory()->create([
            'email' => 'testlogout@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Logout',
            'last_name' => 'Test',
        ]);

        // Login and then logout
        $response = $this->actingAs($user)->get('/auth/logout');

        // Should redirect to login page
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Test that authenticated users can access home
     */
    public function test_authenticated_user_can_access_home()
    {
        // Create required data for home page
        \App\Models\Product::factory()->create();
        \App\Models\InvoiceGroup::factory()->create(['status' => true]);

        // Create a test user
        $user = \App\Models\User::factory()->create([
            'email' => 'testadmin@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Admin',
            'last_name' => 'Test',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test that unauthenticated users cannot access protected routes
     */
    public function test_unauthenticated_user_cannot_access_home()
    {
        $response = $this->call('GET', '/');

        // Should redirect to login
        $this->assertEquals(302, $response->getStatusCode());
    }
}
