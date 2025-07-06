# Guide de Test - Système de Notifications

## 📋 Vue d'ensemble

Ce guide détaille comment tester le système de notifications complet implémenté dans l'application suivi-reformes. Le système comprend :

- ✅ Modèle Notification avec méthodes complètes
- ✅ Service NotificationService pour la logique métier
- ✅ Contrôleur NotificationController avec API REST
- ✅ Interface utilisateur avec dropdown et page dédiée
- ✅ Événements et listeners automatiques
- ✅ Commande de vérification des échéances
- ✅ Intégration RBAC (Role-Based Access Control)

## 🚀 Prérequis

### 1. Installation des dépendances
```bash
# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript (si nécessaire)
npm install
```

### 2. Configuration de la base de données
```bash
# Exécuter les migrations
php artisan migrate

# Exécuter les seeders pour les permissions
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=RolePermissionSeeder
```

### 3. Vérification des fichiers
Assurez-vous que ces fichiers existent :
- `app/Models/Notification.php`
- `app/Services/NotificationService.php`
- `app/Http/Controllers/NotificationController.php`
- `resources/views/notifications/index.blade.php`
- `public/css/notifications-custom.css`
- `public/js/notifications-manager.js`

## 🧪 Tests Fonctionnels

### Test 1: Création manuelle de notifications

#### 1.1 Via Tinker (Console Laravel)
```bash
php artisan tinker
```

```php
// Créer une notification simple
use App\Models\Notification;
use App\Models\User;

$user = User::first();
$notification = Notification::createForUser(
    $user->id,
    'Test de notification manuelle',
    route('dashboard')
);

// Vérifier la création
echo "Notification créée avec l'ID: " . $notification->id;
```

#### 1.2 Via le service
```php
use App\Services\NotificationService;

$service = app(NotificationService::class);

// Notification pour un utilisateur
$notification = $service->createNotification(
    $user->id,
    'Notification via service',
    route('dashboard')
);

// Notification pour un rôle
$service->createNotificationForRole(
    'gestionnaire',
    'Notification pour tous les gestionnaires'
);
```

### Test 2: Interface utilisateur

#### 2.1 Test du dropdown de notifications
1. **Connexion** : Connectez-vous avec un utilisateur
2. **Vérification du header** : 
   - L'icône de notification doit être visible dans le header
   - Le compteur doit afficher le nombre de notifications non lues
3. **Clic sur l'icône** :
   - Le dropdown doit s'ouvrir
   - Les notifications doivent se charger automatiquement
   - Les notifications non lues doivent avoir un style différent

#### 2.2 Test de la page des notifications
1. **Navigation** : Aller sur `/notifications`
2. **Vérifications** :
   - Liste des notifications avec pagination
   - Statistiques (total, non lues, lues, récentes)
   - Boutons d'action (marquer tout comme lu, supprimer les lues)
   - Filtrage par statut

### Test 3: API et AJAX

#### 3.1 Test des endpoints API
```bash
# Obtenir les notifications (nécessite authentification)
curl -X GET "http://localhost:8000/notifications/api" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest"

# Marquer comme lu
curl -X POST "http://localhost:8000/notifications/1/read" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -H "X-CSRF-TOKEN: your-csrf-token"

# Obtenir le nombre non lu
curl -X GET "http://localhost:8000/notifications/unread-count" \
  -H "Accept: application/json"
```

#### 3.2 Test JavaScript
1. **Console du navigateur** :
```javascript
// Tester le gestionnaire de notifications
window.notificationManager.loadNotifications();

// Marquer une notification comme lue
window.notificationManager.markAsRead(1);

// Marquer toutes comme lues
window.notificationManager.markAllAsRead();
```

### Test 4: Événements automatiques

#### 4.1 Test des événements de réforme
```php
// Dans Tinker
use App\Events\ReformeCreated;
use App\Models\Reforme;

$reforme = Reforme::first();
event(new ReformeCreated($reforme, auth()->user()));

// Vérifier que les notifications ont été créées
$notifications = \App\Models\Notification::where('message', 'LIKE', '%' . $reforme->intitule_reforme . '%')->get();
echo "Notifications créées: " . $notifications->count();
```

#### 4.2 Test des événements d'activité
```php
use App\Events\ActiviteCreated;
use App\Models\Activitesreformes;

$activite = Activitesreformes::first();
event(new ActiviteCreated($activite, auth()->user()));
```

### Test 5: Commande de vérification des échéances

#### 5.1 Exécution manuelle
```bash
# Exécuter la commande de vérification
php artisan notifications:check-deadlines

# Avec mode verbose pour plus de détails
php artisan notifications:check-deadlines -v
```

#### 5.2 Test avec données de test
```php
// Créer une activité avec échéance proche
use App\Models\Activitesreformes;
use Carbon\Carbon;

$activite = Activitesreformes::first();
$activite->date_fin_prevue = Carbon::now()->addDays(7);
$activite->save();

// Exécuter la commande
// php artisan notifications:check-deadlines
```

### Test 6: Permissions et rôles

#### 6.1 Test des permissions
```php
// Vérifier les permissions d'un utilisateur
$user = User::first();
$hasPermission = $user->hasPermission('read_notifications');
echo $hasPermission ? 'Autorisé' : 'Non autorisé';
```

#### 6.2 Test des notifications par rôle
```php
use App\Services\NotificationService;

$service = app(NotificationService::class);

// Envoyer à tous les administrateurs
$service->createNotificationForRole('admin', 'Message pour les admins');

// Vérifier que seuls les admins ont reçu la notification
$adminUsers = \App\Models\User::whereHas('roles', function($q) {
    $q->where('role_name', 'admin');
})->pluck('id');

$notifications = \App\Models\Notification::whereIn('user_id', $adminUsers)
    ->where('message', 'Message pour les admins')
    ->count();

echo "Notifications envoyées aux admins: " . $notifications;
```

## 🔧 Tests de Performance

### Test 1: Charge de notifications
```php
// Créer 1000 notifications pour tester la performance
use App\Models\User;
use App\Services\NotificationService;

$service = app(NotificationService::class);
$user = User::first();

$start = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $service->createNotification(
        $user->id,
        "Notification de test #$i"
    );
}

$end = microtime(true);
echo "Temps d'exécution: " . ($end - $start) . " secondes";
```

### Test 2: Chargement de la page
1. Ouvrir les outils de développement du navigateur
2. Aller sur `/notifications`
3. Vérifier les temps de chargement dans l'onglet Network

## 🐛 Dépannage

### Problèmes courants

#### 1. Notifications ne s'affichent pas
- Vérifier que jQuery est chargé
- Vérifier la console JavaScript pour les erreurs
- Vérifier que le CSRF token est présent

#### 2. Erreurs AJAX
- Vérifier les routes dans `routes/web.php`
- Vérifier que l'utilisateur est authentifié
- Vérifier les headers de la requête

#### 3. Événements ne se déclenchent pas
- Vérifier que les listeners sont enregistrés dans `EventServiceProvider`
- Vérifier que les événements sont bien déclenchés dans le code

#### 4. Permissions manquantes
```bash
# Re-exécuter les seeders
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
```

### Logs utiles
```bash
# Voir les logs Laravel
tail -f storage/logs/laravel.log

# Voir les logs de queue (si utilisée)
php artisan queue:work --verbose
```

## 📊 Métriques de test

### Critères de réussite
- ✅ Création de notifications : < 100ms
- ✅ Chargement du dropdown : < 500ms
- ✅ Page des notifications : < 1s
- ✅ Marquage comme lu : < 200ms
- ✅ Événements automatiques : Fonctionnels
- ✅ Permissions respectées : 100%

### Tests de régression
Après chaque modification, vérifier :
1. Création manuelle de notifications
2. Affichage dans l'interface
3. Marquage comme lu/non lu
4. Suppression de notifications
5. Événements automatiques
6. Permissions par rôle

## 🚀 Tests d'intégration

### Scénario complet
1. **Connexion** avec un gestionnaire
2. **Création** d'une nouvelle réforme
3. **Vérification** que les notifications automatiques sont créées
4. **Consultation** des notifications dans le dropdown
5. **Marquage** de certaines comme lues
6. **Navigation** vers la page complète
7. **Filtrage** et gestion des notifications
8. **Test** des fonctionnalités admin (si applicable)

Ce guide couvre tous les aspects du système de notifications. Suivez ces tests pour vous assurer que tout fonctionne correctement.
