<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'utilisateur administrateur par défaut
        $adminUser = User::firstOrCreate([
            'email' => 'admin@suivi-reformes.com'
        ], [
            'name' => 'Administrateur',
            'email' => 'admin@suivi-reformes.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        // Récupérer le rôle admin
        $adminRole = Role::where('role_name', 'admin')->first();

        if ($adminRole && $adminUser) {
            // Associer le rôle admin à l'utilisateur
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
            
            $this->command->info('Utilisateur administrateur créé avec succès !');
            $this->command->info('Email: admin@suivi-reformes.com');
            $this->command->info('Mot de passe: admin123');
        }

        // Créer quelques utilisateurs de test
        $testUsers = [
            [
                'name' => 'Gestionnaire Test',
                'email' => 'gestionnaire@test.com',
                'password' => Hash::make('password123'),
                'role' => 'gestionnaire'
            ],
            [
                'name' => 'Superviseur Test',
                'email' => 'superviseur@test.com',
                'password' => Hash::make('password123'),
                'role' => 'superviseur'
            ],
            [
                'name' => 'Utilisateur Test',
                'email' => 'utilisateur@test.com',
                'password' => Hash::make('password123'),
                'role' => 'utilisateur'
            ]
        ];

        foreach ($testUsers as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'email_verified_at' => now(),
            ]);

            $role = Role::where('role_name', $userData['role'])->first();
            if ($role && $user) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        $this->command->info('Utilisateurs de test créés avec succès !');
    }
}
