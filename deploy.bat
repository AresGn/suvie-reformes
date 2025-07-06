@echo off
echo ========================================
echo DEPLOIEMENT GITLAB - SUIVI DES REFORMES
echo ========================================

echo.
echo Verification de l'etat git...
git status

echo.
echo Ajout des fichiers modifies...
git add app/Http/Controllers/Controller.php
git add app/Http/Controllers/ReformeController.php
git add app/Http/Controllers/ActivitesreformesController.php
git add resources/views/reforme.blade.php
git add resources/views/livewire/activitesreformes.blade.php
git add .gitignore
git add .env.example

echo.
echo Ajout de tous les autres fichiers...
git add .

echo.
echo Fichiers a commiter:
git diff --cached --name-only

echo.
echo Creation du commit...
git commit -m "feat: Ameliorations majeures de l'application de gestion des reformes - Corrections middleware authentification - Ameliorations interface utilisateur - Suppression colonnes createur - Optimisation tri et alignement boutons - Renforcement securite et gestion sessions"

echo.
echo Push vers GitLab...
git push origin dev-don

echo.
echo Deploiement termine!
echo Verifiez sur: https://gitlab.com/stage-insti/suivi-reforme.git

pause
