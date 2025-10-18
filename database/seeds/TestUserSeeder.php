<?php

use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds for test users.
     *
     * @return void
     */
    public function run()
    {
        // Create Lord role
        $lordRole = Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Lord',
            'slug' => 'lord',
        ]);

        // Create Admin role
        $adminRole = Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        // Create test user with ID 3 (used in tests)
        $user1 = Sentinel::registerAndActivate([
            'email' => 'testuser@example.com',
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $user2 = Sentinel::registerAndActivate([
            'email' => 'testuser2@example.com',
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => 'User 2',
        ]);

        $user3 = Sentinel::registerAndActivate([
            'email' => 'testuser3@example.com',
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => 'User 3',
        ]);

        // Attach admin role to user 3
        $adminRole->users()->attach($user3);
    }
}
