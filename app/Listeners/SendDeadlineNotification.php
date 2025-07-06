<?php

namespace App\Listeners;

use App\Events\DeadlineApproaching;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDeadlineNotification implements ShouldQueue
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
    public function handle(DeadlineApproaching $event): void
    {
        $activite = $event->activite;
        $daysRemaining = $event->daysRemaining;

        // Récupérer la réforme associée
        $reforme = $activite->reforme;
        
        if ($reforme) {
            $this->notificationService->notifyDeadlineApproaching(
                $activite->id,
                $activite->intitule_activite,
                $reforme->intitule_reforme,
                $daysRemaining
            );
        }
    }
}
