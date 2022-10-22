<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
    //    Model::unguard();

        $credentials = [
            'email'    => 'admin',
            'password' => 'lotmgeil',
        ];

        $admin = \Sentinel::registerAndActivate($credentials);

        $role = \Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'admin',
            'slug' => 'admin',
        ]);
        $role->users()->attach($admin);

        $credentials = [
            'email'    => 'lord',
            'password' => 'geil',
        ];

        \Sentinel::registerAndActivate($credentials);
    }
}
