<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'admin',
                'description' => 'Administrateur système avec tous les droits'
            ],
            [
                'role_name' => 'gestionnaire',
                'description' => 'Gestionnaire de réformes avec droits de création et modification'
            ],
            [
                'role_name' => 'superviseur',
                'description' => 'Superviseur avec droits de lecture et suivi'
            ],
            [
                'role_name' => 'utilisateur',
                'description' => 'Utilisateur standard avec droits de lecture limités'
            ],
            [
                'role_name' => 'consultant',
                'description' => 'Consultant externe avec accès en lecture seule'
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate([
                'role_name' => $roleData['role_name']
            ], $roleData);
        }

        $this->command->info('Rôles créés avec succès !');
    }
}
