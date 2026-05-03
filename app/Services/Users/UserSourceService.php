<?php

namespace App\Services\Users;

use App\Models\Users\UserSource;
use Exception;
use Illuminate\Support\Facades\DB;

class UserSourceService
{
    public function addSourceToUser($user, $sourceId)
    {
        try {
            $existingRecord = UserSource::query()
                ->where('user_id', $user->id)
                ->where('source_id', $sourceId)
                ->first();

            if ($existingRecord) {
                throw new Exception('Источник уже добавлен пользователю.');
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

                throw new Exception('У пользователя уже есть другие источники: '.implode(', ', $existingSourceIds));
            }

            DB::transaction(function () use ($user, $sourceId) {
                $user->sources()->syncWithoutDetaching([$sourceId]);
            });

            return true;
        } catch (Exception $e) {
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
            throw new Exception('Произошла ошибка при удалении источника у пользователя.');
        }
    }

    public function getUserSources($userId)
    {
        try {
            return UserSource::query()->where('user_id', $userId)->get();
        } catch (Exception $e) {
            throw new Exception('Произошла ошибка при получении источников пользователя.');
        }
    }
}
