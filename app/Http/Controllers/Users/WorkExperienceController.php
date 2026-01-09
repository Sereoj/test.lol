<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkExperience\StoreWorkExperienceRequest;
use App\Http\Requests\WorkExperience\UpdateWorkExperienceRequest;
use App\Http\Resources\WorkExperienceResource;
use App\Services\Users\WorkExperienceService;
use Exception;
use Illuminate\Support\Facades\Auth;

class WorkExperienceController extends Controller
{
    protected WorkExperienceService $workExperienceService;

    public function __construct(WorkExperienceService $workExperienceService)
    {
        $this->workExperienceService = $workExperienceService;
    }

    /**
     * Получить список опыта работы текущего пользователя
     */
    public function index()
    {
        try {
            $workExperiences = $this->workExperienceService->getAll();

            $this->logInfo('Work experiences retrieved', ['user_id' => Auth::id()]);

            return $this->successResponse(WorkExperienceResource::collection($workExperiences));
        } catch (Exception $e) {
            $this->logError('Error retrieving work experiences', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ], $e);

            return $this->errorResponse('Ошибка при получении опыта работы', 500);
        }
    }

    /**
     * Получить опыт работы по ID
     */
    public function show(int $id)
    {
        try {
            $workExperience = $this->workExperienceService->getById($id);

            if (!$workExperience) {
                $this->logError('Work experience not found', ['id' => $id, 'user_id' => Auth::id()]);
                return $this->errorResponse('Опыт работы не найден', 404);
            }

            if ($workExperience->user_id !== Auth::id()) {
                $this->logError('Unauthorized access to work experience', [
                    'id' => $id,
                    'user_id' => Auth::id(),
                    'owner_id' => $workExperience->user_id
                ]);
                return $this->errorResponse('Доступ запрещен', 403);
            }

            $this->logInfo('Work experience retrieved', ['id' => $id, 'user_id' => Auth::id()]);

            return $this->successResponse(new WorkExperienceResource($workExperience));
        } catch (Exception $e) {
            $this->logError('Error retrieving work experience', [
                'id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ], $e);

            return $this->errorResponse('Ошибка при получении опыта работы', 500);
        }
    }

    /**
     * Создать новый опыт работы
     */
    public function store(StoreWorkExperienceRequest $request)
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();

            $workExperience = $this->workExperienceService->create($data);

            $this->logInfo('Work experience created', [
                'id' => $workExperience->id,
                'user_id' => Auth::id()
            ]);

            return $this->successResponse(new WorkExperienceResource($workExperience), 201);
        } catch (Exception $e) {
            $this->logError('Error creating work experience', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ], $e);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Обновить опыт работы
     */
    public function update(UpdateWorkExperienceRequest $request, int $id)
    {
        try {
            $workExperience = $this->workExperienceService->getById($id);

            if (!$workExperience) {
                $this->logError('Work experience not found', ['id' => $id, 'user_id' => Auth::id()]);
                return $this->errorResponse('Опыт работы не найден', 404);
            }

            if ($workExperience->user_id !== Auth::id()) {
                $this->logError('Unauthorized update attempt', [
                    'id' => $id,
                    'user_id' => Auth::id(),
                    'owner_id' => $workExperience->user_id
                ]);
                return $this->errorResponse('Доступ запрещен', 403);
            }

            $data = $request->validated();
            $updatedWorkExperience = $this->workExperienceService->update($id, $data);

            $this->logInfo('Work experience updated', ['id' => $id, 'user_id' => Auth::id()]);

            return $this->successResponse(new WorkExperienceResource($updatedWorkExperience));
        } catch (Exception $e) {
            $this->logError('Error updating work experience', [
                'id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ], $e);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Удалить опыт работы
     */
    public function destroy(int $id)
    {
        try {
            $workExperience = $this->workExperienceService->getById($id);

            if (!$workExperience) {
                $this->logError('Work experience not found', ['id' => $id, 'user_id' => Auth::id()]);
                return $this->errorResponse('Опыт работы не найден', 404);
            }

            if ($workExperience->user_id !== Auth::id()) {
                $this->logError('Unauthorized delete attempt', [
                    'id' => $id,
                    'user_id' => Auth::id(),
                    'owner_id' => $workExperience->user_id
                ]);
                return $this->errorResponse('Доступ запрещен', 403);
            }

            $this->workExperienceService->delete($id);

            $this->logInfo('Work experience deleted', ['id' => $id, 'user_id' => Auth::id()]);

            return $this->successResponse(null, 204);
        } catch (Exception $e) {
            $this->logError('Error deleting work experience', [
                'id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ], $e);

            return $this->errorResponse('Ошибка при удалении опыта работы', 500);
        }
    }
}
