<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\DetecterIndicateursObsoletes;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Planifier la vérification des échéances quotidiennement à 9h00
Schedule::command('notifications:check-deadlines')
    ->dailyAt('09:00')
    ->description('Vérifier les échéances des activités et envoyer des notifications');

// Planifier le nettoyage des sessions chaque dimanche à 2h00
Schedule::command('sessions:cleanup')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->description('Nettoyer les anciennes sessions');

// Planifier la détection des indicateurs obsolètes tous les jours à 8h00
Schedule::command('indicateurs:detecter-obsoletes')
    ->dailyAt('08:00')
    ->description('Détecter les indicateurs obsolètes et envoyer des notifications');

// Planifier une vérification hebdomadaire plus stricte (60 jours) le lundi à 9h00
Schedule::command('indicateurs:detecter-obsoletes --seuil=60')
    ->weekly()
    ->mondays()
    ->at('09:00')
    ->description('Vérification hebdomadaire des indicateurs très obsolètes');
