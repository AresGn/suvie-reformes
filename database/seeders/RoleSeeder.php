<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['role_name' => 'Administrateur'],
            ['role_name' => 'Gestionnaire'],
            ['role_name' => 'Utilisateur'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }
    }
}
