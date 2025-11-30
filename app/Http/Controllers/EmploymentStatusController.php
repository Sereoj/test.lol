<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmploymentStatus\StoreEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\UpdateEmploymentStatusRequest;
use App\Http\Resources\EmploymentStatusResource;
use App\Services\Employment\EmploymentStatusService;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

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
    
    /**
     * @OA\Delete(
     *     path="/api/v1/employment-statuses/{id}",
     *     tags={"EmploymentStatuses"},
     *     summary="Delete employment status",
     *     description="Delete employment status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
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
