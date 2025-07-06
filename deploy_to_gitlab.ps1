# Script de déploiement GitLab pour l'application Suivi des Réformes
# Auteur: TOGBOE-CAKPO
# Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

Write-Host "🚀 DÉPLOIEMENT GITLAB - APPLICATION SUIVI DES RÉFORMES" -ForegroundColor Green
Write-Host "=======================================================" -ForegroundColor Green

# Vérifier que nous sommes dans un dépôt git
if (-not (Test-Path ".git")) {
    Write-Host "❌ Erreur: Pas de dépôt git trouvé dans ce répertoire" -ForegroundColor Red
    exit 1
}

# Afficher l'état actuel
Write-Host "`n📋 ÉTAT ACTUEL DU DÉPÔT" -ForegroundColor Yellow
Write-Host "========================" -ForegroundColor Yellow
git status

# Afficher la branche actuelle
$currentBranch = git branch --show-current
Write-Host "`n🌿 Branche actuelle: $currentBranch" -ForegroundColor Cyan

# Ajouter tous les fichiers modifiés
Write-Host "`n📦 AJOUT DES FICHIERS MODIFIÉS" -ForegroundColor Yellow
Write-Host "===============================" -ForegroundColor Yellow

# Ajouter les fichiers spécifiques que nous avons modifiés
$filesToAdd = @(
    "app/Http/Controllers/Controller.php",
    "app/Http/Controllers/ReformeController.php", 
    "app/Http/Controllers/ActivitesreformesController.php",
    "resources/views/reforme.blade.php",
    "resources/views/livewire/activitesreformes.blade.php",
    ".gitignore",
    ".env.example"
)

foreach ($file in $filesToAdd) {
    if (Test-Path $file) {
        Write-Host "✅ Ajout de: $file" -ForegroundColor Green
        git add $file
    } else {
        Write-Host "⚠️  Fichier non trouvé: $file" -ForegroundColor Yellow
    }
}

# Ajouter tous les autres fichiers modifiés
Write-Host "`n📁 Ajout de tous les autres fichiers modifiés..." -ForegroundColor Cyan
git add .

# Afficher les fichiers qui seront commités
Write-Host "`n📝 FICHIERS À COMMITER" -ForegroundColor Yellow
Write-Host "======================" -ForegroundColor Yellow
git diff --cached --name-only

# Créer le commit avec un message détaillé
$commitMessage = @"
feat: Améliorations majeures de l'application de gestion des réformes

🔧 Corrections techniques:
- Correction du middleware d'authentification dans Controller.php
- Ajout de protection Auth dans ReformeController et ActivitesreformesController
- Correction des contraintes NOT NULL sur created_by avec fallback sécurisé
- Modification du tri des réformes par ID croissant

🎨 Améliorations de l'interface:
- Suppression des colonnes "Créateur" pour une interface plus épurée
- Amélioration de l'alignement des boutons d'action [Voir][Modifier][Supprimer]
- Optimisation de l'espacement et du design des tableaux
- Suppression des références au créateur dans les modals

📁 Configuration:
- Mise à jour du .gitignore avec les meilleures pratiques Laravel
- Amélioration du fichier .env.example

🔒 Sécurité:
- Renforcement de l'authentification avec vérifications Auth::check()
- Gestion gracieuse des sessions expirées
- Logging des problèmes d'authentification

✨ Fonctionnalités:
- Tri des réformes par ID croissant (nouvelles réformes en bas)
- Interface utilisateur plus propre et professionnelle
- Meilleure expérience utilisateur avec boutons alignés
"@

Write-Host "`n💾 CRÉATION DU COMMIT" -ForegroundColor Yellow
Write-Host "====================" -ForegroundColor Yellow
Write-Host "Message du commit:" -ForegroundColor Cyan
Write-Host $commitMessage -ForegroundColor White

# Effectuer le commit
git commit -m $commitMessage

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Commit créé avec succès!" -ForegroundColor Green
} else {
    Write-Host "`n❌ Erreur lors de la création du commit" -ForegroundColor Red
    exit 1
}

# Pousser vers GitLab
Write-Host "`n🚀 PUSH VERS GITLAB" -ForegroundColor Yellow
Write-Host "===================" -ForegroundColor Yellow
Write-Host "Push vers origin/$currentBranch..." -ForegroundColor Cyan

git push origin $currentBranch

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n🎉 DÉPLOIEMENT RÉUSSI!" -ForegroundColor Green
    Write-Host "======================" -ForegroundColor Green
    Write-Host "✅ Tous les changements ont été poussés vers GitLab" -ForegroundColor Green
    Write-Host "🔗 Dépôt: https://gitlab.com/stage-insti/suivi-reforme.git" -ForegroundColor Cyan
    Write-Host "🌿 Branche: $currentBranch" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Erreur lors du push vers GitLab" -ForegroundColor Red
    Write-Host "Vérifiez votre connexion et vos permissions" -ForegroundColor Yellow
    exit 1
}

Write-Host "`n📊 RÉSUMÉ FINAL" -ForegroundColor Yellow
Write-Host "===============" -ForegroundColor Yellow
git log --oneline -3

Write-Host "`n🔍 Pour vérifier sur GitLab:" -ForegroundColor Cyan
Write-Host "https://gitlab.com/stage-insti/suivi-reforme/-/tree/$currentBranch" -ForegroundColor Blue
