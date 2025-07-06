# Script PowerShell pour vérifier l'état Git
Set-Location -Path "."
Write-Host "=== État Git Actuel ===" -ForegroundColor Green
git status

Write-Host "`n=== Fichiers modifiés ===" -ForegroundColor Yellow
git diff --name-only

Write-Host "`n=== Fichiers non suivis ===" -ForegroundColor Cyan
git ls-files --others --exclude-standard

Write-Host "`n=== Derniers commits ===" -ForegroundColor Magenta
git log --oneline -5

Write-Host "`n=== Branche actuelle ===" -ForegroundColor Blue
git branch --show-current

Write-Host "`n=== Remotes configurés ===" -ForegroundColor Red
git remote -v
