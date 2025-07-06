<?php

namespace App\Listeners;

use App\Events\ReformeUpdated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReformeUpdatedNotification implements ShouldQueue
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
    public function handle(ReformeUpdated $event): void
    {
        $reforme = $event->reforme;
        $updatedBy = $event->updatedBy;

        // Notifier les utilisateurs concernés de la mise à jour
        $message = "La réforme '{$reforme->intitule_reforme}' a été mise à jour";
        $url = route('reformes.show', $reforme->id);

        // Notifier les gestionnaires et superviseurs
        $this->notificationService->createNotificationForRole('gestionnaire', $message, $url);
        $this->notificationService->createNotificationForRole('superviseur', $message, $url);
    }
}
