<?php

namespace App\Listeners;

use App\Events\PostCollaboratorAdded;
use App\Models\Users\User;
use App\Notifications\PostCollaboratorAddedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyCollaboratorsOnPostPublished implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(PostCollaboratorAdded $event): void
    {
        $post = $event->post;
        $collaboratorIds = $event->collaboratorIds;

        foreach ($collaboratorIds as $collaboratorId) {
            try {
                $collaborator = User::find($collaboratorId);

                if (!$collaborator) {
                    Log::warning('Соавтор не найден при отправке уведомления', [
                        'collaborator_id' => $collaboratorId,
                        'post_id' => $post->id
                    ]);
                    continue;
                }

                $collaborator->notify(new PostCollaboratorAddedNotification($post));

                Log::info('Уведомление соавтору отправлено', [
                    'collaborator_id' => $collaboratorId,
                    'post_id' => $post->id,
                    'author_id' => $post->user_id
                ]);

            } catch (\Exception $e) {
                Log::error('Не удалось отправить уведомление соавтору', [
                    'collaborator_id' => $collaboratorId,
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
