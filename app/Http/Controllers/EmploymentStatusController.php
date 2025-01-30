<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmploymentStatus\StoreEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\UpdateEmploymentStatusRequest;
use App\Services\EmploymentStatusService;
use Illuminate\Support\Facades\Cache;

class EmploymentStatusController extends Controller
{
    protected EmploymentStatusService $employmentStatusService;

    public function __construct(EmploymentStatusService $employmentStatusService)
    {
        $this->employmentStatusService = $employmentStatusService;
    }

    public function index()
    {
        // Кешируем список статусов трудовой занятости
        $cacheKey = 'employment_statuses';
        if (Cache::has($cacheKey)) {
            // Возвращаем кешированные данные
            return response()->json(Cache::get($cacheKey));
        }

        $employmentStatuses = $this->employmentStatusService->getAllEmploymentStatuses();

        Cache::put($cacheKey, $employmentStatuses, now()->addMinutes(60));

        return response()->json($employmentStatuses);
    }

    public function show($id)
    {
        $employmentStatus = $this->employmentStatusService->getEmploymentStatusById($id);
        if ($employmentStatus) {
            return response()->json($employmentStatus);
        }

        return response()->json(['message' => 'EmploymentStatus not found'], 404);
    }

    public function store(StoreEmploymentStatusRequest $request)
    {
        $data = $request->validated();
        $employmentStatus = $this->employmentStatusService->createEmploymentStatus($data);

        Cache::forget('employment_statuses');

        return response()->json($employmentStatus, 201);
    }

    public function update(UpdateEmploymentStatusRequest $request, $id)
    {
        $data = $request->validated();
        $employmentStatus = $this->employmentStatusService->updateEmploymentStatus($id, $data);
        if ($employmentStatus) {
            Cache::forget('employment_statuses');
            return response()->json($employmentStatus);
        }

        return response()->json(['message' => 'EmploymentStatus not found'], 404);
    }

    public function destroy($id)
    {
        $result = $this->employmentStatusService->deleteEmploymentStatus($id);
        if ($result) {
            Cache::forget('employment_statuses');

            return response()->json(['message' => 'EmploymentStatus deleted successfully']);
        }

        return response()->json(['message' => 'EmploymentStatus not found'], 404);
    }
}
