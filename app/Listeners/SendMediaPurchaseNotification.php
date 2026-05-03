<?php

namespace App\Listeners;

use App\Events\MediaSourcePurchased;
use App\Events\NotificationSent;
use App\Models\Users\User;
use Illuminate\Support\Facades\Log;

class SendMediaPurchaseNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MediaSourcePurchased $event): void
    {
        $mediaPurchase = $event->mediaPurchase;
        $media = $mediaPurchase->media;
        $buyer = $mediaPurchase->user;
        $authorId = $media->user_id;

        // Не отправляем уведомление, если покупатель и автор - один человек
        if ($authorId === $buyer->id) {
            return;
        }

        try {
            $notification = [
                'id' => uniqid(),
                'type' => 'media_purchase',
                'title' => 'Покупка исходника',
                'message' => "{$buyer->username} купил исходник вашего медиа",
                'data' => [
                    'user' => [
                        'id' => $buyer->id,
                        'username' => $buyer->username,
                        'slug' => $buyer->slug ?? $buyer->username,
                        'verification' => $buyer->is_verified ?? false,
                        'avatar' => $buyer->avatar ? [
                            'path' => $buyer->avatar->path ?? '/images/default-avatar.png'
                        ] : [
                            'path' => '/images/default-avatar.png'
                        ]
                    ],
                    'media_id' => $media->id,
                    'amount' => $mediaPurchase->amount,
                ],
                'read_at' => null,
                'created_at' => now()->toIso8601String(),
            ];

            // Отправляем уведомление через WebSocket
            broadcast(new NotificationSent($authorId, $notification));

            Log::info('Уведомление о покупке медиа отправлено', [
                'media_id' => $media->id,
                'author_id' => $authorId,
                'buyer_id' => $buyer->id,
                'purchase_id' => $mediaPurchase->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Не удалось отправить уведомление о покупке медиа: ' . $e->getMessage(), [
                'media_id' => $media->id,
                'author_id' => $authorId,
                'buyer_id' => $buyer->id,
            ]);
        }
    }
}
