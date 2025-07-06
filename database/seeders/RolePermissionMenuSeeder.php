<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\PermissionMenu;

class RolePermissionMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les rôles, permissions et menus
        $adminRole = Role::where('role_name', 'admin')->first();
        $gestionnaireRole = Role::where('role_name', 'gestionnaire')->first();
        $superviseurRole = Role::where('role_name', 'superviseur')->first();
        $utilisateurRole = Role::where('role_name', 'utilisateur')->first();
        $consultantRole = Role::where('role_name', 'consultant')->first();

        // Configuration des permissions par rôle
        $rolePermissions = [
            'admin' => [
                // Toutes les permissions pour l'admin
                'read_reformes', 'create_reformes', 'update_reformes', 'delete_reformes',
                'read_activites', 'create_activites', 'update_activites', 'delete_activites',
                'read_sous_activites', 'create_sous_activites', 'update_sous_activites', 'delete_sous_activites',
                'read_suivi_activites', 'create_suivi_activites', 'update_suivi_activites', 'delete_suivi_activites',
                'read_indicateurs', 'manage_indicateurs',
                'read_rapports', 'read_statistiques', 'generate_pdf', 'read_planning',
                'manage_types_reforme', 'manage_users', 'manage_roles', 'manage_system',
                'view_sessions', 'manage_sessions'
            ],
            'gestionnaire' => [
                // Permissions de gestion pour le gestionnaire
                'read_reformes', 'create_reformes', 'update_reformes',
                'read_activites', 'create_activites', 'update_activites',
                'read_sous_activites', 'create_sous_activites', 'update_sous_activites',
                'read_suivi_activites', 'create_suivi_activites', 'update_suivi_activites',
                'read_indicateurs', 'manage_indicateurs',
                'read_rapports', 'read_statistiques', 'generate_pdf', 'read_planning'
            ],
            'superviseur' => [
                // Permissions de supervision
                'read_reformes', 'read_activites', 'read_sous_activites',
                'read_suivi_activites', 'create_suivi_activites', 'update_suivi_activites',
                'read_indicateurs', 'read_rapports', 'read_statistiques', 'read_planning'
            ],
            'utilisateur' => [
                // Permissions de base
                'read_reformes', 'read_activites', 'read_sous_activites',
                'read_suivi_activites', 'read_indicateurs', 'read_rapports'
            ],
            'consultant' => [
                // Permissions en lecture seule
                'read_reformes', 'read_activites', 'read_rapports', 'read_statistiques'
            ]
        ];

        // Configuration des menus par rôle
        $roleMenus = [
            'admin' => [
                'Tableau de bord', 'Réformes', 'Liste des réformes', 'Ajouter une réforme',
                'Activités', 'Activités principales', 'Sous-activités', 'Suivi des activités',
                'Rapports', 'Statistiques', 'Rapports PDF', 'Planning',
                'Administration', 'Utilisateurs', 'Rôles et permissions', 'Paramètres système'
            ],
            'gestionnaire' => [
                'Tableau de bord', 'Réformes', 'Liste des réformes', 'Ajouter une réforme',
                'Activités', 'Activités principales', 'Sous-activités', 'Suivi des activités',
                'Rapports', 'Statistiques', 'Rapports PDF', 'Planning'
            ],
            'superviseur' => [
                'Tableau de bord', 'Réformes', 'Liste des réformes',
                'Activités', 'Activités principales', 'Sous-activités', 'Suivi des activités',
                'Rapports', 'Statistiques', 'Planning'
            ],
            'utilisateur' => [
                'Tableau de bord', 'Réformes', 'Liste des réformes',
                'Activités', 'Activités principales', 'Suivi des activités',
                'Rapports'
            ],
            'consultant' => [
                'Tableau de bord', 'Réformes', 'Liste des réformes',
                'Activités', 'Activités principales', 'Rapports', 'Statistiques'
            ]
        ];

        // Créer les associations rôle-permission-menu
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('role_name', $roleName)->first();
            if (!$role) continue;

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('permission_name', $permissionName)->first();
                if (!$permission) continue;

                // Associer les menus correspondants à cette permission
                $menusForRole = $roleMenus[$roleName] ?? [];
                foreach ($menusForRole as $menuLibelle) {
                    $menu = Menu::where('libelle', $menuLibelle)->first();
                    if (!$menu) continue;

                    // Créer l'association permission-menu-rôle
                    PermissionMenu::firstOrCreate([
                        'permission_id' => $permission->id,
                        'menu_id' => $menu->id,
                        'role_id' => $role->id
                    ]);
                }
            }
        }

        $this->command->info('Associations rôles-permissions-menus créées avec succès !');
    }
}
