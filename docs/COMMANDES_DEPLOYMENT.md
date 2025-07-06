# Commandes de Déploiement - Système de Suivi des Indicateurs

## 🚀 Installation Rapide

### Étape 1 : Préparation de la Base de Données
```bash
# Exécuter les migrations (si pas déjà fait)
php artisan migrate

# Créer le menu et les permissions pour le suivi des indicateurs
php artisan db:seed --class=SuiviIndicateurMenuSeeder
```

### Étape 2 : Optimisation de l'Application
```bash
# Optimiser les configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Créer les répertoires nécessaires
mkdir -p storage/app/exports
chmod 755 storage/app/exports

# Vérifier les permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Étape 3 : Vérification de l'Installation
```bash
# Vérifier que les routes sont bien enregistrées
php artisan route:list --name=suivi-indicateurs

# Vérifier les événements de notification
php artisan event:list | grep -i indicateur

# Tester la commande de détection des indicateurs obsolètes
php artisan indicateurs:detecter-obsoletes --seuil=30

# Vérifier les tâches programmées
php artisan schedule:list
```

## 📋 Vérifications Post-Installation

### Base de Données
```sql
-- Vérifier que le menu a été créé
SELECT * FROM menu WHERE libelle = 'Suivi des Indicateurs';

-- Vérifier les permissions créées (7 permissions attendues)
SELECT * FROM permission WHERE name LIKE '%indicateur%';

-- Vérifier les associations menu-permissions
SELECT m.libelle, p.name 
FROM menu m 
JOIN permission_menu pm ON m.id = pm.menu_id 
JOIN permission p ON pm.permission_id = p.id 
WHERE m.libelle = 'Suivi des Indicateurs';

-- Vérifier les rôles et permissions
SELECT r.name as role, p.name as permission
FROM role r
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE p.name LIKE '%indicateur%'
ORDER BY r.name, p.name;
```

### Interface Utilisateur
```bash
# Se connecter avec différents rôles et vérifier :

# 1. Admin : Doit voir le menu et avoir tous les droits
# 2. Gestionnaire : Doit voir le menu et avoir tous les droits sauf suppression
# 3. Utilisateur : Doit voir le menu en lecture seule
```

## 🔧 Configuration du Serveur

### Tâches Cron (Obligatoire)
```bash
# Ajouter au crontab du serveur
crontab -e

# Ajouter cette ligne :
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### Configuration Nginx (si applicable)
```nginx
# Ajouter dans la configuration du site
location /suivi-indicateurs {
    try_files $uri $uri/ /index.php?$query_string;
}

location /api/suivi-indicateurs {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 🧪 Tests de Validation

### Test 1 : Accès au Menu
```bash
# URL à tester : http://votre-site.com/suivi-indicateurs
# Doit afficher le dashboard principal avec :
# - Statistiques globales
# - Liste des réformes
# - Système d'alertes
```

### Test 2 : Création d'Évolution
```bash
# 1. Aller sur une réforme spécifique
# 2. Associer un indicateur si pas déjà fait
# 3. Créer une évolution
# 4. Vérifier que la notification est générée
```

### Test 3 : Notifications Automatiques
```bash
# Exécuter manuellement la détection
php artisan indicateurs:detecter-obsoletes --seuil=1

# Vérifier dans la table notifications
SELECT * FROM notifications WHERE type = 'App\\Notifications\\IndicateurObsoleteNotification' ORDER BY created_at DESC LIMIT 5;
```

### Test 4 : Export/Import
```bash
# 1. Exporter les données d'une réforme en CSV
# 2. Vérifier le fichier généré dans storage/app/exports/
# 3. Tester l'import en lot avec un fichier CSV valide
```

## 🚨 Dépannage

### Problème : Menu Non Visible
```bash
# Vérifier les permissions utilisateur
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->roles->pluck('name');
>>> $user->hasPermissionTo('view_suivi_indicateurs');
```

### Problème : Erreur 500 sur les Pages
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions de fichiers
ls -la storage/
ls -la bootstrap/cache/
```

### Problème : Notifications Non Envoyées
```bash
# Vérifier que les événements sont enregistrés
php artisan event:list | grep EvolutionIndicateurCreated

# Vérifier les listeners
php artisan event:list | grep NotifierEvolutionIndicateur

# Tester manuellement un événement
php artisan tinker
>>> event(new App\Events\EvolutionIndicateurCreated(App\Models\EvolutionIndicateur::first()));
```

### Problème : Graphiques Non Affichés
```bash
# Vérifier les routes API
php artisan route:list | grep api/suivi-indicateurs

# Tester l'API directement
curl -H "Accept: application/json" http://votre-site.com/api/suivi-indicateurs/1/graphique
```

## 📊 Monitoring et Maintenance

### Commandes de Maintenance Régulière
```bash
# Nettoyer les anciens fichiers d'export (à exécuter mensuellement)
find storage/app/exports/ -name "*.csv" -mtime +30 -delete

# Optimiser la base de données
php artisan db:show --counts

# Vérifier l'espace disque
df -h storage/
```

### Surveillance des Performances
```bash
# Surveiller les requêtes lentes
# Activer le query log dans config/database.php
'log_queries' => true,

# Vérifier les logs de requêtes
tail -f storage/logs/laravel.log | grep "Query"
```

## 🔄 Mise à Jour

### Après Modification du Code
```bash
# Nettoyer les caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recréer les caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Après Modification de la Base de Données
```bash
# Exécuter les nouvelles migrations
php artisan migrate

# Réexécuter les seeders si nécessaire
php artisan db:seed --class=SuiviIndicateurMenuSeeder
```

## ✅ Checklist de Déploiement

### Avant le Déploiement
- [ ] Code testé en local
- [ ] Migrations créées et testées
- [ ] Seeders fonctionnels
- [ ] Tests unitaires passent
- [ ] Documentation à jour

### Pendant le Déploiement
- [ ] `php artisan migrate` exécuté
- [ ] `php artisan db:seed --class=SuiviIndicateurMenuSeeder` exécuté
- [ ] Caches optimisés
- [ ] Permissions de fichiers correctes
- [ ] Cron configuré

### Après le Déploiement
- [ ] Menu visible selon les rôles
- [ ] Pages principales accessibles
- [ ] Notifications fonctionnelles
- [ ] API endpoints répondent
- [ ] Graphiques s'affichent
- [ ] Export/Import fonctionnent
- [ ] Tâches automatiques programmées

## 📞 Support

### Logs à Consulter
```bash
# Logs généraux
tail -f storage/logs/laravel.log

# Logs spécifiques aux indicateurs
tail -f storage/logs/laravel.log | grep -i indicateur

# Logs des notifications
tail -f storage/logs/laravel.log | grep -i notification
```

### Commandes de Diagnostic
```bash
# État général de l'application
php artisan about

# Vérifier la connectivité base de données
php artisan tinker
>>> DB::connection()->getPdo()

# Tester les modèles
>>> App\Models\ReformeIndicateur::count()
>>> App\Models\EvolutionIndicateur::count()
```

---

**Note :** Ce système est maintenant entièrement intégré avec le système de notifications existant et respecte l'architecture RBAC en place. Toutes les fonctionnalités sont opérationnelles après l'exécution de ces commandes.
