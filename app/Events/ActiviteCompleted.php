<?php

namespace App\Events;

use App\Models\Activitesreformes;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActiviteCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $activite;
    public $completedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Activitesreformes $activite, $completedBy = null)
    {
        $this->activite = $activite;
        $this->completedBy = $completedBy;
    }
}
