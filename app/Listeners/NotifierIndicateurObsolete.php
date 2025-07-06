<?php

namespace App\Listeners;

use App\Events\IndicateurObsolete;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifierIndicateurObsolete implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(IndicateurObsolete $event): void
    {
        $reformeIndicateur = $event->reformeIndicateur;
        $joursDepuisDerniereEvolution = $event->joursDepuisDerniereEvolution;
        
        $reforme = $reformeIndicateur->reforme;
        $indicateur = $reformeIndicateur->indicateur;

        // Déterminer le niveau d'urgence selon le nombre de jours
        $typeNotification = 'warning';
        $icone = 'fa-clock';
        $niveauUrgence = 'Attention';
        
        if ($joursDepuisDerniereEvolution > 60) {
            $typeNotification = 'error';
            $icone = 'fa-exclamation-triangle';
            $niveauUrgence = 'URGENT';
        } elseif ($joursDepuisDerniereEvolution > 90) {
            $typeNotification = 'error';
            $icone = 'fa-ban';
            $niveauUrgence = 'CRITIQUE';
        }

        // Créer le message de notification
        $titre = "Indicateur obsolète détecté";
        $message = sprintf(
            "%s : L'indicateur '%s' de la réforme '%s' n'a pas été mis à jour depuis %d jours. Une mise à jour est nécessaire pour maintenir un suivi efficace.",
            $niveauUrgence,
            $indicateur->nom,
            $reforme->libelle,
            $joursDepuisDerniereEvolution
        );

        $url = route('suivi-indicateurs.creer-evolution', $reformeIndicateur->id);

        // Notifier principalement les gestionnaires et administrateurs
        $utilisateursANotifier = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['gestionnaire', 'admin']);
        })->get();

        foreach ($utilisateursANotifier as $utilisateur) {
            $this->notificationService->creerNotification(
                $utilisateur->id,
                $titre,
                $message,
                $typeNotification,
                $url,
                $icone
            );
        }
    }
}
