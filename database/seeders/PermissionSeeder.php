<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Permissions pour les réformes
            'read_reformes',
            'create_reformes',
            'update_reformes',
            'delete_reformes',
            
            // Permissions pour les activités
            'read_activites',
            'create_activites',
            'update_activites',
            'delete_activites',
            
            // Permissions pour les sous-activités
            'read_sous_activites',
            'create_sous_activites',
            'update_sous_activites',
            'delete_sous_activites',
            
            // Permissions pour le suivi des activités
            'read_suivi_activites',
            'create_suivi_activites',
            'update_suivi_activites',
            'delete_suivi_activites',
            
            // Permissions pour les indicateurs
            'read_indicateurs',
            'manage_indicateurs',
            
            // Permissions pour les rapports
            'read_rapports',
            'read_statistiques',
            'generate_pdf',
            'read_planning',
            
            // Permissions pour les types de réforme
            'manage_types_reforme',
            
            // Permissions d'administration
            'manage_users',
            'manage_roles',
            'manage_system',
            
            // Permissions pour les sessions
            'view_sessions',
            'manage_sessions',

            // Permissions pour les notifications
            'read_notifications',
            'manage_notifications',
            'send_notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'permission_name' => $permission
            ]);
        }

        $this->command->info('Permissions créées avec succès !');
    }
}
