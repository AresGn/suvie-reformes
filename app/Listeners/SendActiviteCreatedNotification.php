<?php

namespace App\Listeners;

use App\Events\ActiviteCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendActiviteCreatedNotification implements ShouldQueue
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
    public function handle(ActiviteCreated $event): void
    {
        $activite = $event->activite;
        $createdBy = $event->createdBy;

        // Récupérer la réforme associée
        $reforme = $activite->reforme;
        
        if ($reforme) {
            $this->notificationService->notifyActiviteCreated(
                $activite->id,
                $activite->intitule_activite,
                $reforme->intitule_reforme
            );
        }
    }
}
