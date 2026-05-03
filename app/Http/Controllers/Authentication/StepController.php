<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Media\AvatarController;
use App\Http\Requests\Avatar\UploadAvatarRequest;
use App\Http\Requests\Step\StepOneRequest;
use App\Http\Requests\Step\StepTwoRequest;
use App\Models\Users\User;
use App\Services\Media\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class StepController extends Controller
{
    protected AvatarService $avatarService;
    private AvatarController $avatarController;

    public function __construct(AvatarService $avatarService, AvatarController $avatarController)
    {
        $this->avatarService = $avatarService;
        $this->avatarController = $avatarController;
    }

    // Шаг 1: Добавление источников
    public function one(StepOneRequest $request)
    {
        try {
            $request->user()->sources()->syncWithoutDetaching($request->get('source_id'));

            Log::info('Первый шаг онбординга пользователя завершен', [
                'user_id' => Auth::id(),
                'source_id' => $request->get('source_id')
            ]);

            return $this->successResponse(['message' => 'Source successfully added']);
        } catch (Exception $e) {
            Log::error('Ошибка завершения первого шага онбординга: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'source_id' => $request->get('source_id')
            ]);

            return $this->errorResponse('Failed to complete step one. Please try again.', 500);
        }
    }

    // Шаг 2: Добавление навыков
    public function two(StepTwoRequest $request)
    {
        try {
            $request->user()->skills()->syncWithoutDetaching($request->get('skill_ids'));

            Log::info('Второй шаг онбординга пользователя завершен', [
                'user_id' => Auth::id(),
                'skill_ids' => $request->get('skill_ids')
            ]);

            return $this->successResponse(['message' => 'Skills successfully added']);
        } catch (Exception $e) {
            Log::error('Ошибка завершения второго шага онбординга: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'skill_ids' => $request->get('skill_ids')
            ]);

            return $this->errorResponse('Failed to complete step two. Please try again.', 500);
        }
    }

    // Шаг 3: Загрузка аватара
    public function three(UploadAvatarRequest $request)
    {
        try {
            Log::info('Инициализация третьего шага онбординга (загрузка аватара)', [
                'user_id' => Auth::id()
            ]);

            return $this->avatarController->uploadAvatar($request);
        } catch (Exception $e) {
            Log::error('Ошибка в третьем шаге онбординга: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);

            return $this->errorResponse('Failed to upload avatar. Please try again.', 500);
        }
    }
}
