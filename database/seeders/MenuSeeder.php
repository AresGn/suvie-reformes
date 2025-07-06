<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'libelle' => 'Tableau de bord',
                'url' => '/dashboard',
                'icon' => 'educate-icon educate-home',
                'ordre' => 1
            ],
            [
                'libelle' => 'Réformes',
                'url' => '/reformes',
                'icon' => 'educate-icon educate-library',
                'ordre' => 2
            ],
            [
                'libelle' => 'Liste des réformes',
                'url' => '/reformes',
                'icon' => 'fa fa-list-ul',
                'ordre' => 21,
                'parent_id' => 2
            ],
            [
                'libelle' => 'Ajouter une réforme',
                'url' => '/reformes/create',
                'icon' => 'fa fa-plus',
                'ordre' => 22,
                'parent_id' => 2
            ],
            [
                'libelle' => 'Activités',
                'url' => '/activites',
                'icon' => 'educate-icon educate-course',
                'ordre' => 3
            ],
            [
                'libelle' => 'Activités principales',
                'url' => '/activites',
                'icon' => 'fa fa-tasks',
                'ordre' => 31,
                'parent_id' => 5
            ],
            [
                'libelle' => 'Sous-activités',
                'url' => '/sous-activites',
                'icon' => 'fa fa-sitemap',
                'ordre' => 32,
                'parent_id' => 5
            ],
            [
                'libelle' => 'Suivi des activités',
                'url' => '/suivi-activites',
                'icon' => 'educate-icon educate-analytics',
                'ordre' => 4
            ],
            [
                'libelle' => 'Rapports',
                'url' => '/rapports',
                'icon' => 'educate-icon educate-charts',
                'ordre' => 5
            ],
            [
                'libelle' => 'Statistiques',
                'url' => '/rapports/statistiques',
                'icon' => 'fa fa-bar-chart',
                'ordre' => 51,
                'parent_id' => 9
            ],
            [
                'libelle' => 'Rapports PDF',
                'url' => '/rapports/pdf',
                'icon' => 'fa fa-file-pdf-o',
                'ordre' => 52,
                'parent_id' => 9
            ],
            [
                'libelle' => 'Planning',
                'url' => '/rapports/planning',
                'icon' => 'fa fa-calendar',
                'ordre' => 53,
                'parent_id' => 9
            ],
            [
                'libelle' => 'Administration',
                'url' => '/admin',
                'icon' => 'educate-icon educate-settings',
                'ordre' => 6
            ],
            [
                'libelle' => 'Utilisateurs',
                'url' => '/utilisateurs',
                'icon' => 'fa fa-users',
                'ordre' => 61,
                'parent_id' => 13
            ],
            [
                'libelle' => 'Rôles et permissions',
                'url' => '/role',
                'icon' => 'fa fa-shield',
                'ordre' => 62,
                'parent_id' => 13
            ],
            [
                'libelle' => 'Paramètres système',
                'url' => '/admin/settings',
                'icon' => 'fa fa-cog',
                'ordre' => 63,
                'parent_id' => 13
            ]
        ];

        foreach ($menus as $menuData) {
            Menu::firstOrCreate([
                'libelle' => $menuData['libelle'],
                'url' => $menuData['url']
            ], $menuData);
        }

        $this->command->info('Menus créés avec succès !');
    }
}
