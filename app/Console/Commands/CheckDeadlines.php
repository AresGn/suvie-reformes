<?php

namespace App\Console\Commands;

use App\Events\DeadlineApproaching;
use App\Models\Activitesreformes;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier les échéances des activités et envoyer des notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification des échéances en cours...');

        // Définir les seuils d'alerte (en jours)
        $alertThresholds = [30, 15, 7, 3, 1];

        foreach ($alertThresholds as $days) {
            $this->checkActivitiesWithDeadline($days);
        }

        $this->info('Vérification terminée.');
    }

    /**
     * Vérifier les activités avec une échéance dans X jours
     */
    private function checkActivitiesWithDeadline(int $days)
    {
        $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');
        
        $activites = Activitesreformes::whereDate('date_fin_prevue', $targetDate)
            ->where('statut', '!=', 'Terminé')
            ->with('reforme')
            ->get();

        foreach ($activites as $activite) {
            // Vérifier si une notification pour cette échéance n'a pas déjà été envoyée
            if (!$this->hasRecentDeadlineNotification($activite, $days)) {
                event(new DeadlineApproaching($activite, $days));
                
                $this->info("Notification d'échéance envoyée pour l'activité: {$activite->intitule_activite} ({$days} jours restants)");
            }
        }
    }

    /**
     * Vérifier si une notification récente pour cette échéance existe déjà
     */
    private function hasRecentDeadlineNotification($activite, $days): bool
    {
        $searchMessage = "échéance dans {$days} jour";
        $activiteTitle = $activite->intitule_activite;
        
        // Chercher une notification similaire dans les dernières 24 heures
        $recentNotification = \App\Models\Notification::where('message', 'LIKE', "%{$searchMessage}%")
            ->where('message', 'LIKE', "%{$activiteTitle}%")
            ->where('date_notification', '>=', Carbon::now()->subDay())
            ->exists();

        return $recentNotification;
    }
}
