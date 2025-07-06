# Système de Contrôle d'Accès Basé sur les Rôles (RBAC)

## Vue d'ensemble

Ce document décrit l'implémentation complète du système de contrôle d'accès basé sur les rôles (RBAC) pour l'application de suivi des réformes. Le système permet de contrôler l'accès aux menus et fonctionnalités selon les rôles et permissions attribués aux utilisateurs.

## Architecture du système

### Modèles principaux

1. **User** - Utilisateurs du système
2. **Role** - Rôles attribués aux utilisateurs
3. **Permission** - Permissions spécifiques
4. **Menu** - Éléments de menu de l'application
5. **PermissionMenu** - Table pivot associant permissions, menus et rôles

### Relations

- Un utilisateur peut avoir plusieurs rôles (Many-to-Many)
- Un rôle peut avoir plusieurs permissions via les menus (Many-to-Many through PermissionMenu)
- Un menu peut être associé à plusieurs permissions et rôles

## Fonctionnalités implémentées

### 1. Méthodes d'autorisation dans le modèle User

```php
// Vérifier si l'utilisateur a un rôle spécifique
$user->hasRole('admin')

// Vérifier si l'utilisateur a une permission
$user->hasPermission('read_reformes')

// Vérifier l'accès à un menu
$user->canAccessMenu($menuId)

// Récupérer les menus accessibles
$user->getAccessibleMenus()

// Vérifier si l'utilisateur est administrateur
$user->isAdmin()
```

### 2. Service de gestion des permissions

Le `PermissionService` centralise la logique de vérification des permissions :

```php
// Injection du service
$permissionService = app(PermissionService::class);

// Vérifications
$permissionService->hasRole('admin');
$permissionService->hasPermission('read_reformes');
$permissionService->canAccessMenu($menuId);
$permissionService->isAdmin();

// Méthodes CRUD
$permissionService->canCreate('reformes');
$permissionService->canRead('reformes');
$permissionService->canUpdate('reformes');
$permissionService->canDelete('reformes');
```

### 3. Middlewares de protection

#### RoleMiddleware
Protège les routes basées sur les rôles :
```php
Route::middleware('role:admin')->group(function () {
    // Routes réservées aux administrateurs
});
```

#### RolePermissionMiddleware
Protège les routes basées sur les permissions ou menus :
```php
// Protection par permission
Route::middleware('role.permission:permission:read_reformes')->group(function () {
    // Routes nécessitant la permission read_reformes
});

// Protection par menu
Route::middleware('role.permission:menu:1')->group(function () {
    // Routes nécessitant l'accès au menu ID 1
});
```

### 4. Directives Blade personnalisées

```blade
{{-- Vérification de rôle --}}
@hasRole('admin')
    <div>Contenu administrateur</div>
@endhasRole

{{-- Vérification de permission --}}
@hasPermission('read_reformes')
    <a href="{{ route('reformes.index') }}">Liste des réformes</a>
@endhasPermission

{{-- Vérification d'accès au menu --}}
@canAccessMenu(1)
    <li><a href="/menu-item">Menu Item</a></li>
@endcanAccessMenu

{{-- Vérification administrateur --}}
@isAdmin
    <div>Panneau d'administration</div>
@endisAdmin

{{-- Directives CRUD --}}
@canCreate('reformes')
    <button>Créer une réforme</button>
@endcanCreate

@canUpdate('reformes')
    <button>Modifier</button>
@endcanUpdate

@canDelete('reformes')
    <button>Supprimer</button>
@endcanDelete
```

## Configuration et installation

### 1. Exécuter les migrations

```bash
php artisan migrate
```

### 2. Exécuter les seeders

```bash
php artisan db:seed
```

Cela créera :
- Les permissions de base
- Les rôles par défaut (admin, gestionnaire, superviseur, utilisateur, consultant)
- Les menus de l'application
- Les associations rôles-permissions-menus
- Un utilisateur administrateur par défaut

### 3. Utilisateur administrateur par défaut

- **Email** : admin@suivi-reformes.com
- **Mot de passe** : admin123

## Rôles et permissions par défaut

### Rôles disponibles

1. **admin** - Administrateur système avec tous les droits
2. **gestionnaire** - Gestionnaire de réformes avec droits de création/modification
3. **superviseur** - Superviseur avec droits de lecture et suivi
4. **utilisateur** - Utilisateur standard avec droits de lecture limités
5. **consultant** - Consultant externe avec accès en lecture seule

### Permissions disponibles

- **Réformes** : read_reformes, create_reformes, update_reformes, delete_reformes
- **Activités** : read_activites, create_activites, update_activites, delete_activites
- **Suivi** : read_suivi_activites, create_suivi_activites, update_suivi_activites, delete_suivi_activites
- **Rapports** : read_rapports, read_statistiques, generate_pdf, read_planning
- **Administration** : manage_users, manage_roles, manage_system
- **Et plus...**

## Tests

Le système inclut des tests complets :

```bash
# Exécuter tous les tests
php artisan test

# Tests spécifiques
php artisan test tests/Feature/UserPermissionTest.php
php artisan test tests/Feature/PermissionServiceTest.php
php artisan test tests/Feature/PermissionMiddlewareTest.php
php artisan test tests/Feature/BladeDirectivesTest.php
```

## Utilisation dans les contrôleurs

```php
class ReformeController extends Controller
{
    public function index()
    {
        // Vérification automatique via middleware
        // ou vérification manuelle :
        if (!auth()->user()->hasPermission('read_reformes')) {
            abort(403);
        }
        
        // Logique du contrôleur...
    }
}
```

## Gestion des sessions

Le système stocke automatiquement en session :
- Les rôles de l'utilisateur
- Ses permissions
- Les menus accessibles

Cela améliore les performances en évitant les requêtes répétées à la base de données.

## Maintenance

### Ajouter une nouvelle permission

1. Créer la permission dans la base de données
2. L'associer aux rôles appropriés via PermissionMenu
3. Mettre à jour les vues et routes si nécessaire

### Ajouter un nouveau rôle

1. Créer le rôle dans la base de données
2. Définir ses permissions via PermissionMenu
3. Mettre à jour les seeders si nécessaire

## Sécurité

- Toutes les routes sensibles sont protégées par middleware
- Les vérifications de permissions sont centralisées
- Les données de session sont automatiquement nettoyées à la déconnexion
- Les tests garantissent le bon fonctionnement du système
