<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'id' => 1,
            'personne_id' => 1,
            'pwd' => Hash::make('password1'),
            'status' => 1,
        ]);

        User::create([
            'id' => 2,
            'personne_id' => 3,
            'pwd' => Hash::make('password4'),
            'status' => 1,
        ]);

        User::create([
            'id' => 3,
            'personne_id' => 4,
            'pwd' => Hash::make('password5'),
            'status' => 1,
        ]);
    }
}
