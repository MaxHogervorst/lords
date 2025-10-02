<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        User::create(['email' => 'admin', 'password' => Hash::make('lotmgeil')]);
        User::create(['email' => 'lord', 'password' => Hash::make('geil')]);
    }
}
