<?php

namespace App\Events;

use App\Models\Billing\MediaPurchase;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaSourcePurchased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MediaPurchase $mediaPurchase;

    /**
     * Create a new event instance.
     */
    public function __construct(MediaPurchase $mediaPurchase)
    {
        $this->mediaPurchase = $mediaPurchase;
    }
}
