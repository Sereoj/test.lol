<?php

namespace App\Services;

use App\Models\Source;
use App\Models\UserSource;
use Exception;

class UserSourceService
{
    public function addSourceToUser($userId, $sourceId)
    {
        $source = Source::findOrFail($sourceId);

        // Проверяем, существует ли уже запись для данного пользователя и источника
        $existingRecord = UserSource::query()->where('user_id', $userId)
            ->where('source_id', $sourceId)
            ->first();

        if ($existingRecord) {
            throw new Exception('The source is already added to the user.');
        }

        return UserSource::create([
            'user_id' => $userId,
            'source_id' => $source->id,
        ]);
    }

    public function removeSourceFromUser($userId, $sourceId)
    {
        try {
            $userSource = UserSource::query()->where('user_id', $userId)
                ->where('source_id', $sourceId)
                ->firstOrFail();

            return $userSource->delete();
        } catch (Exception $e) {
            throw new Exception('An error occurred while removing the source from the user.');
        }
    }

    public function getUserSources($userId)
    {
        try {
            return UserSource::query()->where('user_id', $userId)->get();
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving the user sources.');
        }
    }
}
