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
     * @OA\Get(
     *     path="/api/v1/employment-statuses",
     *     tags={"EmploymentStatuses"},
     *     summary="Get all employment statuses",
     *     description="Get all employment statuses",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/EmploymentStatus")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function index()
    {
        $employmentStatuses = $this->getFromCacheOrStore(self::CACHE_KEY_EMPLOYMENT_STATUSES, self::CACHE_MINUTES, function () {
            return EmploymentStatusResource::collection($this->employmentStatusService->getAllEmploymentStatuses());
        });

        return $this->successResponse($employmentStatuses);
    }

    // Получение конкретного трудового статуса
    /**
     * @OA\Get(
     *     path="/api/v1/employment-statuses/{id}",
     *     tags={"EmploymentStatuses"},
     *     summary="Get employment status by ID",
     *     description="Get employment status by ID",
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
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EmploymentStatus")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function show($id)
    {
        $employmentStatus = $this->employmentStatusService->getEmploymentStatusById($id);

        if ($employmentStatus) {
            return $this->successResponse($employmentStatus);
        }

        return $this->errorResponse('EmploymentStatus not found', 404);
    }

    // Создание нового трудового статуса
    /**
     * @OA\Post(
     *     path="/api/v1/employment-statuses",
     *     tags={"EmploymentStatuses"},
     *     summary="Create new employment status",
     *     description="Create new employment status",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreEmploymentStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EmploymentStatus")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function store(StoreEmploymentStatusRequest $request)
    {
        $data = $request->validated();
        $employmentStatus = $this->employmentStatusService->createEmploymentStatus($data);

        $this->forgetCache(self::CACHE_KEY_EMPLOYMENT_STATUSES);

        return $this->successResponse($employmentStatus,[], 201);
    }

    // Обновление трудового статуса
    /**
     * @OA\Put(
     *     path="/api/v1/employment-statuses/{id}",
     *     tags={"EmploymentStatuses"},
     *     summary="Update employment status",
     *     description="Update employment status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEmploymentStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EmploymentStatus")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
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
