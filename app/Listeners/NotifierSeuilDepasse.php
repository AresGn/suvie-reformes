<?php

namespace App\Listeners;

use App\Events\IndicateurSeuilDepasse;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifierSeuilDepasse implements ShouldQueue
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
    public function handle(IndicateurSeuilDepasse $event): void
    {
        $evolution = $event->evolution;
        $typeAlerte = $event->typeAlerte;
        $seuil = $event->seuil;
        
        $reformeIndicateur = $evolution->reformeIndicateur;
        $reforme = $reformeIndicateur->reforme;
        $indicateur = $reformeIndicateur->indicateur;

        // Déterminer le type de notification et l'icône selon le type d'alerte
        $typeNotification = 'warning';
        $icone = 'fa-exclamation-triangle';
        
        if ($typeAlerte === 'critique') {
            $typeNotification = 'error';
            $icone = 'fa-exclamation-circle';
        } elseif ($typeAlerte === 'amelioration') {
            $typeNotification = 'success';
            $icone = 'fa-check-circle';
        }

        // Créer le message selon le type d'alerte
        $titre = "Seuil d'indicateur dépassé";
        
        switch ($typeAlerte) {
            case 'critique':
                $message = sprintf(
                    "ALERTE CRITIQUE : L'indicateur '%s' de la réforme '%s' a atteint une valeur critique de %s (seuil : %s).",
                    $indicateur->nom,
                    $reforme->libelle,
                    number_format($evolution->valeur, 2),
                    number_format($seuil, 2)
                );
                break;
                
            case 'amelioration':
                $message = sprintf(
                    "EXCELLENTE NOUVELLE : L'indicateur '%s' de la réforme '%s' a dépassé l'objectif avec une valeur de %s (objectif : %s).",
                    $indicateur->nom,
                    $reforme->libelle,
                    number_format($evolution->valeur, 2),
                    number_format($seuil, 2)
                );
                break;
                
            default:
                $message = sprintf(
                    "L'indicateur '%s' de la réforme '%s' a dépassé le seuil d'alerte avec une valeur de %s (seuil : %s).",
                    $indicateur->nom,
                    $reforme->libelle,
                    number_format($evolution->valeur, 2),
                    number_format($seuil, 2)
                );
        }

        $url = route('suivi-indicateurs.tableau-bord', $reforme->id);

        // Notifier tous les utilisateurs ayant accès au suivi des indicateurs
        $utilisateursANotifier = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['gestionnaire', 'admin', 'utilisateur']);
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
