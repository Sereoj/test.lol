<?php

namespace App\Services;

use App\Models\UserSource;
use Exception;
use Illuminate\Support\Facades\DB;

class UserSourceService
{
    public function addSourceToUser($user, $sourceId)
    {
        try {
            DB::beginTransaction();
            $existingRecord = UserSource::query()
                ->where('user_id', $user->id)
                ->where('source_id', $sourceId)
                ->first();

            if ($existingRecord) {
                throw new Exception('The source is already added to the user.');
            }

            // Проверяем, есть ли другие источники у пользователя
            $otherSources = UserSource::query()
                ->where('user_id', $user->id)
                ->where('source_id', '<>', $sourceId)
                ->exists();

            if ($otherSources) {
                $existingSourceIds = UserSource::query()
                    ->where('user_id', $user->id)
                    ->pluck('source_id')
                    ->toArray();

                throw new Exception('The user already has other sources: '.implode(', ', $existingSourceIds));
            }

            $user->sources()->syncWithoutDetaching([$sourceId]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
