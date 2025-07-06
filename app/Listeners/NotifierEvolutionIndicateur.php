<?php

namespace App\Listeners;

use App\Events\EvolutionIndicateurCreated;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifierEvolutionIndicateur implements ShouldQueue
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
    public function handle(EvolutionIndicateurCreated $event): void
    {
        $evolution = $event->evolution;
        $reformeIndicateur = $evolution->reformeIndicateur;
        $reforme = $reformeIndicateur->reforme;
        $indicateur = $reformeIndicateur->indicateur;

        // Calculer la variation par rapport à la valeur précédente
        $variationPrecedente = $evolution->variation_precedente;
        $typeVariation = $evolution->type_variation;

        // Déterminer le type de notification selon la variation
        $typeNotification = 'info';
        $icone = 'fa-chart-line';
        
        if ($typeVariation === 'hausse' && $variationPrecedente > 10) {
            $typeNotification = 'success';
            $icone = 'fa-arrow-up';
        } elseif ($typeVariation === 'baisse' && $variationPrecedente < -10) {
            $typeNotification = 'warning';
            $icone = 'fa-arrow-down';
        }

        // Créer le message de notification
        $titre = "Nouvelle évolution d'indicateur";
        $message = sprintf(
            "L'indicateur '%s' de la réforme '%s' a été mis à jour avec une valeur de %s (%s de %s%%).",
            $indicateur->nom,
            $reforme->libelle,
            number_format($evolution->valeur, 2),
            $typeVariation,
            abs($variationPrecedente)
        );

        $url = route('suivi-indicateurs.tableau-bord', $reforme->id);

        // Notifier les gestionnaires et administrateurs
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
