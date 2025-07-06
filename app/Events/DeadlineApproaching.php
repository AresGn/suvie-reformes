<?php

namespace App\Events;

use App\Models\Activitesreformes;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeadlineApproaching
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $activite;
    public $daysRemaining;

    /**
     * Create a new event instance.
     */
    public function __construct(Activitesreformes $activite, int $daysRemaining)
    {
        $this->activite = $activite;
        $this->daysRemaining = $daysRemaining;
    }
}
