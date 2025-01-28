<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmploymentStatus\StoreEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\UpdateEmploymentStatusRequest;
use App\Services\EmploymentStatusService;

class EmploymentStatusController extends Controller
{
    protected EmploymentStatusService $employmentStatusService;

    public function __construct(EmploymentStatusService $employmentStatusService)
    {
        $this->employmentStatusService = $employmentStatusService;
    }

    public function index()
    {
        $employmentStatuses = $this->employmentStatusService->getAllEmploymentStatuses();
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
        return response()->json($employmentStatus, 201);
    }

    public function update(UpdateEmploymentStatusRequest $request, $id)
    {
        $data = $request->validated();
        $employmentStatus = $this->employmentStatusService->updateEmploymentStatus($id, $data);
        if ($employmentStatus) {
            return response()->json($employmentStatus);
        }
        return response()->json(['message' => 'EmploymentStatus not found'], 404);
    }

    public function destroy($id)
    {
        $result = $this->employmentStatusService->deleteEmploymentStatus($id);
        if ($result) {
            return response()->json(['message' => 'EmploymentStatus deleted successfully']);
        }
        return response()->json(['message' => 'EmploymentStatus not found'], 404);
    }
}
