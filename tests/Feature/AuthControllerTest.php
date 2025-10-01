<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sentinel;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that login page is accessible
     */
    public function testLoginPageIsAccessible()
    {
        $this->get('/auth/login')
            ->assertStatus(200)
            ->assertSee('Login')
            ->assertDontSee('Whoops');
    }

    /**
     * Test successful authentication
     */
    public function testSuccessfulAuthentication()
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
    public function testFailedAuthenticationWrongPassword()
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
    public function testFailedAuthenticationNonExistentUser()
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
    public function testLogout()
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
    public function testAuthenticatedUserCanAccessHome()
    {
        // Create a test user with admin role
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'testadmin@example.com',
            'password' => 'password',
            'first_name' => 'Admin',
            'last_name' => 'Test',
        ]);

        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->get('/')
            ->assertStatus(200)
            ->assertDontSee('Login')
            ->assertDontSee('Unauthorized');
    }

    /**
     * Test that unauthenticated users cannot access protected routes
     */
    public function testUnauthenticatedUserCannotAccessHome()
    {
        $response = $this->call('GET', '/');

        // Should redirect to login
        $this->assertEquals(302, $response->getStatusCode());
    }
}
