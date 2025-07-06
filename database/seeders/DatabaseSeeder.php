<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Ordre important : d'abord les entités de base
            PermissionSeeder::class,
            RoleSeeder::class,
            MenuSeeder::class,

            // Ensuite les associations
            RolePermissionMenuSeeder::class,

            // Enfin les utilisateurs
            UsersTableSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
