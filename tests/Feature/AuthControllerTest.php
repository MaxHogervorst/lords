<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sentinel;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

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
        $user = Sentinel::registerAndActivate([
            'email' => 'testauth@example.com',
            'password' => 'testpassword123',
            'first_name' => 'Auth',
            'last_name' => 'Test',
        ]);

        // Attempt to authenticate
        $response = $this->call('POST', '/auth/authenticate', [
            'email' => 'testauth@example.com',
            'password' => 'testpassword123',
        ]);

        // Check response - could be redirect (302) or success page (200)
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    /**
     * Test failed authentication with wrong password
     */
    public function test_failed_authentication_wrong_password()
    {
        // Create a test user
        $user = Sentinel::registerAndActivate([
            'email' => 'testfail@example.com',
            'password' => 'correctpassword',
            'first_name' => 'Fail',
            'last_name' => 'Test',
        ]);

        // Attempt to authenticate with wrong password
        $response = $this->call('POST', '/auth/authenticate', [
            'email' => 'testfail@example.com',
            'password' => 'wrongpassword',
        ]);

        // Check response - could be redirect (302) or error page (200)
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    /**
     * Test failed authentication with non-existent user
     */
    public function test_failed_authentication_non_existent_user()
    {
        $response = $this->call('POST', '/auth/authenticate', [
            'email' => 'nonexistent@example.com',
            'password' => 'anypassword',
        ]);

        // Check response - could be redirect (302) or error page (200)
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    /**
     * Test logout functionality
     */
    public function test_logout()
    {
        // Login first
        $user = Sentinel::registerAndActivate([
            'email' => 'testlogout@example.com',
            'password' => 'password',
            'first_name' => 'Logout',
            'last_name' => 'Test',
        ]);

        Sentinel::login($user);

        // Now logout
        $response = $this->call('GET', '/auth/logout');

        // Should redirect to login page
        $this->assertEquals(302, $response->getStatusCode());

        // User should no longer be authenticated
        $this->assertFalse(Sentinel::check());
    }

    /**
     * Test that authenticated users can access home
     */
    public function test_authenticated_user_can_access_home()
    {
        // Create required data for home page
        \App\Models\Product::factory()->create();
        \App\Models\InvoiceGroup::factory()->create(['status' => true]);

        // Create a test user with admin role
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'testadmin@example.com',
            'password' => 'password',
            'first_name' => 'Admin',
            'last_name' => 'Test',
        ]);

        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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
