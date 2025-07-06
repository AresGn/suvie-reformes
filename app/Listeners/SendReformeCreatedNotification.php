<?php

namespace App\Listeners;

use App\Events\ReformeCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReformeCreatedNotification implements ShouldQueue
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
    public function handle(ReformeCreated $event): void
    {
        $reforme = $event->reforme;
        $createdBy = $event->createdBy;

        // Notifier les gestionnaires et superviseurs de la nouvelle réforme
        $this->notificationService->notifyReformeCreated(
            $reforme->id,
            $reforme->intitule_reforme,
            $createdBy
        );
    }
}
