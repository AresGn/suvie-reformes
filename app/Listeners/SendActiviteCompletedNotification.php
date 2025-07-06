<?php

namespace App\Listeners;

use App\Events\ActiviteCompleted;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendActiviteCompletedNotification implements ShouldQueue
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
    public function handle(ActiviteCompleted $event): void
    {
        $activite = $event->activite;
        $completedBy = $event->completedBy;

        // Récupérer la réforme associée
        $reforme = $activite->reforme;
        
        if ($reforme) {
            $message = "L'activité '{$activite->intitule_activite}' de la réforme '{$reforme->intitule_reforme}' a été terminée";
            $url = route('reformes.show', $reforme->id);

            // Notifier les gestionnaires et superviseurs
            $this->notificationService->createNotificationForRole('gestionnaire', $message, $url);
            $this->notificationService->createNotificationForRole('superviseur', $message, $url);
        }
    }
}
