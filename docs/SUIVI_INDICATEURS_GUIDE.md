# Guide Complet du Système de Suivi des Indicateurs

## Vue d'ensemble

Le système de suivi des indicateurs permet de monitorer l'évolution des indicateurs de performance des réformes avec des fonctionnalités avancées de :
- Suivi temporel des évolutions
- Calcul automatique des variations et tendances
- Alertes automatiques sur les seuils critiques
- Notifications intégrées pour les gestionnaires
- Tableaux de bord interactifs avec graphiques
- Import/Export CSV pour la gestion en lot
- Détection automatique des indicateurs obsolètes

## Architecture du Système

### Modèles de Données

#### 1. ReformeIndicateur (Pivot Model)
- **Table** : `reformes_indicateurs`
- **Clés primaires composites** : `reforme_id`, `indicateur_id`
- **Relations** :
  - `reforme()` : Appartient à une réforme
  - `indicateur()` : Appartient à un indicateur
  - `evolutions()` : A plusieurs évolutions

#### 2. EvolutionIndicateur
- **Table** : `evolution_indicateur`
- **Clés primaires composites** : `reforme_indicateur_id`, `date_evolution`
- **Champs calculés automatiquement** :
  - `variation_precedente` : Pourcentage de variation
  - `type_variation` : 'hausse', 'baisse', 'stable'

### Services

#### SuiviIndicateurService
Service principal contenant toute la logique métier :
- Gestion des associations indicateur-réforme
- Création et mise à jour des évolutions
- Calculs statistiques et tendances
- Génération de rapports
- Import/Export CSV
- Détection d'indicateurs obsolètes

### Contrôleurs

#### SuiviIndicateurController
Contrôleur principal avec 15+ méthodes :
- Pages principales (index, tableau de bord, alertes)
- CRUD des évolutions
- Gestion des associations
- API pour les graphiques
- Import/Export

## Installation et Configuration

### 1. Commandes à Exécuter

```bash
# Exécuter les migrations (si pas déjà fait)
php artisan migrate

# Créer le menu et les permissions
php artisan db:seed --class=SuiviIndicateurMenuSeeder

# Vérifier que les événements sont bien enregistrés
php artisan event:list

# Tester la commande de détection des indicateurs obsolètes
php artisan indicateurs:detecter-obsoletes --seuil=30

# Programmer les tâches automatiques (déjà configuré dans routes/console.php)
# - Détection quotidienne à 8h00
# - Vérification hebdomadaire le lundi à 9h00
```

### 2. Permissions Créées

Le système crée automatiquement ces permissions :
- `view_suivi_indicateurs` : Voir le suivi des indicateurs
- `manage_suivi_indicateurs` : Gérer le suivi des indicateurs
- `create_evolution_indicateurs` : Créer des évolutions d'indicateurs
- `edit_evolution_indicateurs` : Modifier des évolutions d'indicateurs
- `delete_evolution_indicateurs` : Supprimer des évolutions d'indicateurs
- `export_indicateurs` : Exporter les données d'indicateurs
- `import_indicateurs` : Importer des données d'indicateurs

### 3. Attribution des Rôles

- **Admin** : Toutes les permissions
- **Gestionnaire** : Toutes sauf suppression
- **Utilisateur** : Lecture seule

## Utilisation du Système

### 1. Accès au Système

Le menu "Suivi des Indicateurs" apparaît automatiquement pour les utilisateurs ayant les permissions appropriées.

**URL principale** : `/suivi-indicateurs`

### 2. Pages Principales

#### Dashboard Principal (`/suivi-indicateurs`)
- Vue d'ensemble de toutes les réformes
- Statistiques globales
- Alertes système
- Score de suivi par réforme

#### Tableau de Bord Réforme (`/suivi-indicateurs/reforme/{id}`)
- Statistiques spécifiques à la réforme
- Graphiques d'évolution
- Liste des indicateurs associés
- Actions rapides

#### Page des Alertes (`/suivi-indicateurs/alertes`)
- Indicateurs obsolètes
- Seuils dépassés
- Réformes sans indicateurs

### 3. Gestion des Évolutions

#### Créer une Évolution
```
URL : /suivi-indicateurs/{reformeIndicateur}/evolution/creer
```
- Formulaire avec validation en temps réel
- Calcul automatique des variations
- Historique des évolutions précédentes

#### Modifier une Évolution
```
URL : /suivi-indicateurs/{reformeIndicateur}/evolution/{date}/modifier
```
- Modification des valeurs existantes
- Recalcul automatique des variations

### 4. Import/Export

#### Export CSV
```
URL : /suivi-indicateurs/reforme/{reforme}/export-csv
```
Génère un fichier CSV avec :
- Nom de l'indicateur
- Date d'évolution
- Valeur
- Variation (%)
- Type de variation

#### Import en Lot
```
URL : /suivi-indicateurs/import-lot
```
Permet d'importer plusieurs évolutions via CSV.

## Système de Notifications

### Événements Automatiques

#### 1. EvolutionIndicateurCreated
Déclenché à chaque création d'évolution :
- Notifie les gestionnaires et admins
- Inclut les détails de la variation
- Lien direct vers le tableau de bord

#### 2. IndicateurSeuilDepasse
Déclenché selon les seuils configurés :
- **Critique** : Baisse > 20%
- **Amélioration** : Hausse > 15%
- **Alerte** : Variation > 10%

#### 3. IndicateurObsolete
Déclenché pour les indicateurs non mis à jour :
- **Attention** : 30+ jours
- **Urgent** : 60+ jours
- **Critique** : 90+ jours

### Configuration des Seuils

Les seuils sont configurables dans `SuiviIndicateurService::verifierSeuils()` :
```php
$seuilCritique = 20;      // Variation critique
$seuilAmelioration = 15;  // Amélioration notable
$seuilAlerte = 10;        // Alerte générale
```

## API Endpoints

### Données de Graphiques
```
GET /api/suivi-indicateurs/{reformeIndicateur}/graphique
```
Retourne les données pour Chart.js.

### Statistiques Réforme
```
GET /api/suivi-indicateurs/reforme/{reforme}/statistiques
```
Retourne les statistiques en temps réel.

## Tâches Automatisées

### Détection des Indicateurs Obsolètes

#### Configuration dans routes/console.php :
```php
// Quotidien à 8h00
Schedule::command('indicateurs:detecter-obsoletes')
    ->dailyAt('08:00');

// Hebdomadaire le lundi à 9h00 (seuil 60 jours)
Schedule::command('indicateurs:detecter-obsoletes --seuil=60')
    ->weekly()->mondays()->at('09:00');
```

#### Commande manuelle :
```bash
php artisan indicateurs:detecter-obsoletes --seuil=30
```

## Sécurité et Permissions

### Middleware Appliqué
```php
Route::middleware('role:gestionnaire,admin')
```

### Vérifications dans les Vues
```php
@can('manage_suivi_indicateurs')
    <!-- Actions de gestion -->
@endcan
```

## Dépannage

### Problèmes Courants

1. **Menu non visible** : Vérifier les permissions utilisateur
2. **Graphiques non chargés** : Vérifier les routes API
3. **Notifications non envoyées** : Vérifier la configuration des événements
4. **Import CSV échoue** : Vérifier le format et les permissions

### Logs à Consulter
- `storage/logs/laravel.log` : Erreurs générales
- Queue logs : Pour les notifications asynchrones

### Commandes de Diagnostic
```bash
# Vérifier les routes
php artisan route:list --name=suivi-indicateurs

# Vérifier les événements
php artisan event:list

# Vérifier les permissions
php artisan tinker
>>> App\Models\User::find(1)->roles
```

## Maintenance

### Nettoyage Périodique
- Archivage des anciennes évolutions
- Nettoyage des fichiers d'export temporaires
- Optimisation des requêtes de statistiques

### Monitoring
- Surveillance des performances des requêtes
- Monitoring des notifications envoyées
- Vérification de l'espace disque pour les exports

## Commandes de Déploiement Complètes

### Installation Initiale
```bash
# 1. Exécuter les migrations (si pas déjà fait)
php artisan migrate

# 2. Créer le menu et les permissions
php artisan db:seed --class=SuiviIndicateurMenuSeeder

# 3. Optimiser l'application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Créer les répertoires nécessaires
mkdir -p storage/app/exports
chmod 755 storage/app/exports

# 5. Vérifier les permissions des fichiers
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Tests Post-Installation
```bash
# Vérifier les routes
php artisan route:list --name=suivi-indicateurs

# Vérifier les événements
php artisan event:list | grep -i indicateur

# Tester la détection des indicateurs obsolètes
php artisan indicateurs:detecter-obsoletes --seuil=30

# Vérifier les tâches programmées
php artisan schedule:list
```

### Configuration du Serveur Web

#### Apache (.htaccess)
```apache
# Déjà configuré dans public/.htaccess
# Vérifier que mod_rewrite est activé
```

#### Nginx
```nginx
# Configuration pour les routes du suivi des indicateurs
location /suivi-indicateurs {
    try_files $uri $uri/ /index.php?$query_string;
}

# Configuration pour les API
location /api/suivi-indicateurs {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Configuration des Tâches Cron
```bash
# Ajouter au crontab du serveur
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### Vérification de la Configuration
```bash
# Vérifier la configuration Laravel
php artisan about

# Vérifier les permissions de base de données
php artisan tinker
>>> DB::connection()->getPdo()

# Tester une requête simple
>>> App\Models\Reforme::count()
```
