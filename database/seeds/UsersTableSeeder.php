<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Rúben',
            'email' => 'ruben.estevao@slingshot.pt',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
    }
}
