<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmploymentStatus\StoreEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\UpdateEmploymentStatusRequest;
use App\Http\Resources\EmploymentStatusResource;
use App\Services\Employment\EmploymentStatusService;
use Illuminate\Support\Facades\Cache;

// Контроллер для работы с трудовым статусом
class EmploymentStatusController extends Controller
{
    protected EmploymentStatusService $employmentStatusService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_EMPLOYMENT_STATUSES = 'employment_statuses';

    public function __construct(EmploymentStatusService $employmentStatusService)
    {
        $this->employmentStatusService = $employmentStatusService;
    }

    // Получение списка всех трудовых статусов
    public function index()
    {
        $employmentStatuses = $this->getFromCacheOrStore(self::CACHE_KEY_EMPLOYMENT_STATUSES, self::CACHE_MINUTES, function () {
            return EmploymentStatusResource::collection($this->employmentStatusService->getAllEmploymentStatuses());
        });

        return $this->successResponse($employmentStatuses);
    }

    // Получение конкретного трудового статуса
    public function show($id)
    {
        $employmentStatus = $this->employmentStatusService->getEmploymentStatusById($id);

        if ($employmentStatus) {
            return $this->successResponse($employmentStatus);
        }

        return $this->errorResponse('EmploymentStatus not found', 404);
    }

    // Создание нового трудового статуса
    public function store(StoreEmploymentStatusRequest $request)
    {
        $data = $request->validated();
        $employmentStatus = $this->employmentStatusService->createEmploymentStatus($data);

        $this->forgetCache(self::CACHE_KEY_EMPLOYMENT_STATUSES);

        return $this->successResponse($employmentStatus,[], 201);
    }

    // Обновление трудового статуса
    public function update(UpdateEmploymentStatusRequest $request, $id)
    {
        $data = $request->validated();
        $employmentStatus = $this->employmentStatusService->updateEmploymentStatus($id, $data);

        if ($employmentStatus) {
            $this->forgetCache(self::CACHE_KEY_EMPLOYMENT_STATUSES);

            return $this->successResponse($employmentStatus);
        }

        return $this->errorResponse('EmploymentStatus not found', 404);
    }

    // Удаление трудового статуса   
    public function destroy($id)
    {
        $result = $this->employmentStatusService->deleteEmploymentStatus($id);

        if ($result) {
            $this->forgetCache(self::CACHE_KEY_EMPLOYMENT_STATUSES);

            return $this->successResponse(['message' => 'EmploymentStatus deleted successfully']);
        }

        return $this->errorResponse('EmploymentStatus not found', 404);
    }
}
