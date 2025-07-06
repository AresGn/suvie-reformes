<?php

namespace App\Events;

use App\Models\Activitesreformes;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActiviteCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $activite;
    public $createdBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Activitesreformes $activite, $createdBy = null)
    {
        $this->activite = $activite;
        $this->createdBy = $createdBy;
    }
}
