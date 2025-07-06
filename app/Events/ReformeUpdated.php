<?php

namespace App\Events;

use App\Models\Reforme;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReformeUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reforme;
    public $updatedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Reforme $reforme, $updatedBy = null)
    {
        $this->reforme = $reforme;
        $this->updatedBy = $updatedBy;
    }
}
