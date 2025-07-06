<?php

namespace App\Events;

use App\Models\Reforme;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReformeCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reforme;
    public $createdBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Reforme $reforme, $createdBy = null)
    {
        $this->reforme = $reforme;
        $this->createdBy = $createdBy;
    }
}
