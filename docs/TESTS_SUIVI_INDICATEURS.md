# Guide de Tests - Système de Suivi des Indicateurs

## Tests d'Installation et Configuration

### 1. Vérification de l'Installation

#### Commandes à Exécuter
```bash
# 1. Exécuter le seeder pour créer le menu et permissions
php artisan db:seed --class=SuiviIndicateurMenuSeeder

# 2. Vérifier que les routes sont bien enregistrées
php artisan route:list --name=suivi-indicateurs

# 3. Vérifier les événements
php artisan event:list | grep -i indicateur

# 4. Tester la commande de détection
php artisan indicateurs:detecter-obsoletes --seuil=30
```

#### Vérifications en Base de Données
```sql
-- Vérifier que le menu a été créé
SELECT * FROM menu WHERE libelle = 'Suivi des Indicateurs';

-- Vérifier les permissions créées
SELECT * FROM permission WHERE name LIKE '%indicateur%';

-- Vérifier les associations menu-permissions
SELECT m.libelle, p.name 
FROM menu m 
JOIN permission_menu pm ON m.id = pm.menu_id 
JOIN permission p ON pm.permission_id = p.id 
WHERE m.libelle = 'Suivi des Indicateurs';
```

### 2. Test des Permissions par Rôle

#### Test Admin
1. Se connecter avec un compte admin
2. Vérifier que le menu "Suivi des Indicateurs" est visible
3. Accéder à `/suivi-indicateurs` - doit fonctionner
4. Tester toutes les actions (créer, modifier, supprimer, exporter, importer)

#### Test Gestionnaire
1. Se connecter avec un compte gestionnaire
2. Vérifier l'accès au menu
3. Tester les actions autorisées (tout sauf suppression)
4. Vérifier que la suppression est interdite

#### Test Utilisateur
1. Se connecter avec un compte utilisateur simple
2. Vérifier l'accès en lecture seule
3. Vérifier que les boutons d'action sont masqués

## Tests Fonctionnels

### 1. Test du Dashboard Principal

#### URL : `/suivi-indicateurs`

**Éléments à Vérifier :**
- [ ] Affichage des statistiques globales
- [ ] Liste des réformes avec scores
- [ ] Système d'alertes fonctionnel
- [ ] Liens vers les tableaux de bord spécifiques
- [ ] Mise à jour automatique toutes les 5 minutes

**Test de Performance :**
- Temps de chargement < 2 secondes
- Pas d'erreurs JavaScript dans la console

### 2. Test du Tableau de Bord Réforme

#### URL : `/suivi-indicateurs/reforme/{id}`

**Éléments à Vérifier :**
- [ ] Statistiques spécifiques à la réforme
- [ ] Graphiques Chart.js fonctionnels
- [ ] Liste des indicateurs associés
- [ ] Boutons d'action selon les permissions
- [ ] Modales d'association/dissociation

**Test des Graphiques :**
```javascript
// Vérifier dans la console du navigateur
console.log(window.chartInstances); // Doit contenir les graphiques
```

### 3. Test de Gestion des Évolutions

#### Création d'Évolution
**URL :** `/suivi-indicateurs/{reformeIndicateur}/evolution/creer`

**Scénarios de Test :**
1. **Création normale :**
   - Saisir une valeur valide
   - Vérifier le calcul automatique de variation
   - Confirmer la sauvegarde

2. **Validation des erreurs :**
   - Valeur négative (si non autorisée)
   - Date future
   - Date déjà existante
   - Champs obligatoires vides

3. **Calcul des variations :**
   - Première évolution : variation = null
   - Deuxième évolution : calcul correct du pourcentage
   - Type de variation correct (hausse/baisse/stable)

#### Modification d'Évolution
**URL :** `/suivi-indicateurs/{reformeIndicateur}/evolution/{date}/modifier`

**Tests :**
- [ ] Chargement des données existantes
- [ ] Modification et recalcul des variations
- [ ] Impact sur les évolutions suivantes

### 4. Test des Associations Indicateur-Réforme

#### Association
**URL :** `POST /suivi-indicateurs/reforme/{reforme}/associer`

**Tests :**
- [ ] Association d'un nouvel indicateur
- [ ] Prévention des doublons
- [ ] Mise à jour de l'interface

#### Dissociation
**URL :** `GET /suivi-indicateurs/reforme/{reforme}/indicateur/{indicateur}/dissocier`

**Tests :**
- [ ] Confirmation avant suppression
- [ ] Suppression des évolutions associées
- [ ] Mise à jour de l'interface

### 5. Test Import/Export

#### Export CSV
**URL :** `/suivi-indicateurs/reforme/{reforme}/export-csv`

**Vérifications :**
- [ ] Génération du fichier CSV
- [ ] Format correct des données
- [ ] Nom de fichier avec timestamp
- [ ] Téléchargement automatique

**Format Attendu :**
```csv
Indicateur,Date,Valeur,Variation (%),Type Variation
"Taux de satisfaction",2024-01-15,85.5,5.2,hausse
```

#### Import en Lot
**URL :** `/suivi-indicateurs/import-lot`

**Tests :**
1. **Import valide :**
   - Fichier CSV bien formaté
   - Données cohérentes
   - Traitement sans erreur

2. **Gestion d'erreurs :**
   - Format de fichier incorrect
   - Données manquantes
   - Conflits de dates

## Tests des Notifications

### 1. Test des Événements

#### EvolutionIndicateurCreated
**Déclenchement :** Création d'une nouvelle évolution

**Vérifications :**
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log | grep EvolutionIndicateurCreated
```

**Test Manuel :**
1. Créer une évolution
2. Vérifier qu'une notification est créée
3. Vérifier le contenu de la notification

#### IndicateurSeuilDepasse
**Déclenchement :** Variation importante

**Tests :**
1. **Seuil critique (baisse > 20%) :**
   - Créer évolution avec baisse de 25%
   - Vérifier notification de type "error"

2. **Seuil amélioration (hausse > 15%) :**
   - Créer évolution avec hausse de 18%
   - Vérifier notification de type "success"

3. **Seuil alerte (variation > 10%) :**
   - Créer évolution avec variation de 12%
   - Vérifier notification de type "warning"

#### IndicateurObsolete
**Déclenchement :** Commande manuelle ou automatique

**Test :**
```bash
# Exécuter la commande
php artisan indicateurs:detecter-obsoletes --seuil=30

# Vérifier les notifications créées
```

### 2. Test des Listeners

#### Vérification des Notifications Créées
```sql
-- Vérifier les notifications récentes
SELECT * FROM notifications 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY created_at DESC;
```

## Tests API

### 1. API Graphiques

#### Endpoint : `/api/suivi-indicateurs/{reformeIndicateur}/graphique`

**Test avec curl :**
```bash
curl -H "Accept: application/json" \
     -H "Authorization: Bearer {token}" \
     http://localhost/api/suivi-indicateurs/1/graphique
```

**Réponse Attendue :**
```json
{
  "labels": ["2024-01-01", "2024-01-15", "2024-02-01"],
  "datasets": [{
    "label": "Évolution",
    "data": [80, 85, 82],
    "borderColor": "rgb(75, 192, 192)"
  }]
}
```

### 2. API Statistiques

#### Endpoint : `/api/suivi-indicateurs/reforme/{reforme}/statistiques`

**Test :**
```bash
curl -H "Accept: application/json" \
     http://localhost/api/suivi-indicateurs/reforme/1/statistiques
```

**Réponse Attendue :**
```json
{
  "total_indicateurs": 5,
  "indicateurs_recents": 3,
  "score_suivi": 75,
  "derniere_mise_a_jour": "2024-01-15"
}
```

## Tests de Performance

### 1. Test de Charge

#### Dashboard Principal
```bash
# Test avec Apache Bench (si disponible)
ab -n 100 -c 10 http://localhost/suivi-indicateurs
```

#### Requêtes de Statistiques
- Mesurer le temps d'exécution des requêtes complexes
- Vérifier l'utilisation des index de base de données

### 2. Test de Mémoire

#### Monitoring pendant l'import
```bash
# Surveiller l'utilisation mémoire pendant un gros import
memory_get_peak_usage(true);
```

## Tests de Sécurité

### 1. Test d'Autorisation

#### Accès Non Autorisé
```bash
# Tenter d'accéder sans authentification
curl http://localhost/suivi-indicateurs
# Doit rediriger vers login

# Tenter d'accéder avec un rôle insuffisant
# Doit retourner 403 Forbidden
```

### 2. Test de Validation

#### Injection de Données
- Tester avec des valeurs extrêmes
- Vérifier la sanitisation des entrées
- Tester les attaques XSS dans les formulaires

## Tests d'Intégration

### 1. Test avec le Système de Notifications

**Scénario Complet :**
1. Créer une réforme
2. Associer des indicateurs
3. Créer des évolutions
4. Vérifier les notifications générées
5. Marquer les notifications comme lues

### 2. Test avec le Système RBAC

**Vérifications :**
- Cohérence des permissions
- Héritage des rôles
- Restrictions d'accès

## Checklist de Validation Finale

### Installation
- [ ] Seeder exécuté sans erreur
- [ ] Menu visible selon les rôles
- [ ] Routes accessibles
- [ ] Permissions correctement attribuées

### Fonctionnalités
- [ ] Dashboard principal fonctionnel
- [ ] Tableaux de bord spécifiques
- [ ] CRUD des évolutions
- [ ] Associations indicateur-réforme
- [ ] Import/Export CSV
- [ ] Graphiques interactifs

### Notifications
- [ ] Événements déclenchés
- [ ] Listeners fonctionnels
- [ ] Notifications créées
- [ ] Contenu correct des messages

### Performance
- [ ] Temps de réponse acceptables
- [ ] Pas de fuites mémoire
- [ ] Requêtes optimisées

### Sécurité
- [ ] Autorisations respectées
- [ ] Validation des données
- [ ] Protection CSRF
