@echo off
title Deploiement Automatique GitLab
color 0B

echo.
echo ╔══════════════════════════════════════╗
echo ║     DEPLOIEMENT AUTOMATIQUE GITLAB   ║
echo ║        Suivi des Reformes            ║
echo ╚══════════════════════════════════════╝
echo.

echo 🔍 Verification du projet...
if not exist ".git" (
    echo ❌ Erreur: Pas de depot git trouve
    pause
    exit /b 1
)

echo ✅ Depot git detecte
echo.

echo 📦 Ajout de TOUS les fichiers modifies...
git add .
echo ✅ Fichiers ajoutes
echo.

echo 📝 Creation du commit automatique...
set "timestamp=%date% %time%"
git commit -m "feat: Deploiement automatique - Ameliorations interface et securite - %timestamp%"
echo ✅ Commit cree
echo.

echo 🚀 Push vers GitLab (branche dev-don)...
git push origin dev-don
echo.

if %errorlevel% equ 0 (
    echo ╔══════════════════════════════════════╗
    echo ║         DEPLOIEMENT REUSSI!          ║
    echo ╚══════════════════════════════════════╝
    echo.
    echo 🔗 Verifiez sur: https://gitlab.com/stage-insti/suivi-reforme/-/tree/dev-don
    echo.
    echo 📊 Derniers commits:
    git log --oneline -3
) else (
    echo ╔══════════════════════════════════════╗
    echo ║         ERREUR DEPLOIEMENT           ║
    echo ╚══════════════════════════════════════╝
    echo ❌ Verifiez votre connexion internet
)

echo.
echo Appuyez sur une touche pour fermer...
pause >nul
