<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmploymentStatus\AssignEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\RemoveEmploymentStatusRequest;
use App\Http\Resources\EmploymentStatusResource;
use App\Http\Resources\UserEmploymentStatusResource;
use App\Services\Users\UserEmploymentStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class UserEmploymentStatusController extends Controller
{
    protected UserEmploymentStatusService $userEmploymentStatusService;

    private const CACHE_KEY_USER_EMPLOYMENT_STATUS = 'user_employment_status_';

    public function __construct(UserEmploymentStatusService $userEmploymentStatusService)
    {
        $this->userEmploymentStatusService = $userEmploymentStatusService;
    }

    public function index()
    {

       $employmentStatuses = new UserEmploymentStatusResource($this->userEmploymentStatusService->getAllEmploymentStatuses());

        return $this->successResponse($employmentStatuses);
    }

    /**
     * Назначить трудовой статус пользователю.
     */
    public function assignEmploymentStatus(AssignEmploymentStatusRequest $request)
    {
        try {
            $user = Auth::user();
            $employmentStatusId = $request->input('employment_status_id');

            $user = $this->userEmploymentStatusService->assignEmploymentStatusToUser($user->id, $employmentStatusId);

            if ($user) {
                $this->forgetCache(self::CACHE_KEY_USER_EMPLOYMENT_STATUS . $user->id);

                Log::info('Трудовой статус успешно назначен', [
                    'user_id' => $user->id,
                    'employment_status_id' => $employmentStatusId
                ]);

                return $this->successResponse('Employment status assigned successfully');
            }

            Log::warning('Пользователь или трудовой статус не найден', [
                'user_id' => Auth::id(),
                'employment_status_id' => $employmentStatusId
            ]);

            return $this->errorResponse('User or EmploymentStatus not found', 404);
        } catch (Exception $e) {
            Log::error('Ошибка при назначении трудового статуса: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'employment_status_id' => $request->input('employment_status_id')
            ]);
            return $this->errorResponse('An error occurred while assigning employment status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Удалить трудовой статус пользователя.
     */
    public function removeEmploymentStatus(RemoveEmploymentStatusRequest $request)
    {
        try {
            $user = Auth::user();

            $user = $this->userEmploymentStatusService->removeEmploymentStatusFromUser($user->id);

            if ($user) {
                $this->forgetCache(self::CACHE_KEY_USER_EMPLOYMENT_STATUS . $user->id);

                Log::info('Трудовой статус успешно удален', ['user_id' => $user->id]);

                return $this->successResponse('Employment status removed successfully');
            }

            Log::warning('Пользователь не найден', ['user_id' => Auth::id()]);

            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            Log::error('Ошибка при удалении трудового статуса: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while removing employment status: ' . $e->getMessage(), 500);
        }
    }
}
