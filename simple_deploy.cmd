@echo off
title Deploiement GitLab - Suivi des Reformes
color 0A

echo.
echo ==========================================
echo   DEPLOIEMENT GITLAB - SUIVI DES REFORMES
echo ==========================================
echo.

echo [1/7] Verification de l'etat git...
git status
echo.

echo [2/7] Ajout des controleurs...
git add app/Http/Controllers/Controller.php
git add app/Http/Controllers/ReformeController.php
git add app/Http/Controllers/ActivitesreformesController.php
echo Controleurs ajoutes.
echo.

echo [3/7] Ajout des vues...
git add resources/views/reforme.blade.php
git add resources/views/livewire/activitesreformes.blade.php
echo Vues ajoutees.
echo.

echo [4/7] Ajout des fichiers de configuration...
git add .gitignore
git add .env.example
echo Configuration ajoutee.
echo.

echo [5/7] Verification des fichiers a commiter...
git diff --cached --name-only
echo.

echo [6/7] Creation du commit...
git commit -m "feat: Ameliorations majeures interface et securite - Corrections middleware authentification - Suppression colonnes createur - Optimisation alignement boutons - Tri par ID croissant"
echo.

echo [7/7] Push vers GitLab...
git push origin dev-don
echo.

if %errorlevel% equ 0 (
    echo ==========================================
    echo   DEPLOIEMENT REUSSI !
    echo ==========================================
    echo.
    echo Verifiez sur: https://gitlab.com/stage-insti/suivi-reforme/-/tree/dev-don
) else (
    echo ==========================================
    echo   ERREUR LORS DU DEPLOIEMENT
    echo ==========================================
    echo Verifiez votre connexion internet et vos permissions GitLab
)

echo.
echo Appuyez sur une touche pour fermer...
pause >nul
