<?php

namespace App\Http\Controllers\Statuses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Statuses\StoreStatusRequest;
use App\Http\Requests\Status\UpdateStatusRequest;
use App\Http\Resources\StatusResource;
use App\Services\StatusService;
use OpenApi\Attributes as OA;

// Контроллер для работы со статусами
class StatusController extends Controller
{
    protected StatusService $statusService;

    private const CACHE_KEY_STATUSES_LIST = 'statuses_list';
    private const CACHE_KEY_STATUS = 'status_';
    private const CACHE_MINUTES = 60;

    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    // Получение списка всех статусов   
    /**
     * @OA\Get(
     *     path="/api/v1/statuses",
     *     tags={"Statuses"},
     *     summary="Get all statuses",
     *     description="Get all statuses",
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
     *                 @OA\Items(ref="#/components/schemas/Status")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function index()
    {
        $statuses = $this->getFromCacheOrStore(self::CACHE_KEY_STATUSES_LIST, self::CACHE_MINUTES, function () {
            return $this->statusService->getAll();
        });

        return $this->successResponse(StatusResource::collection($statuses));
    }

    // Создание нового статуса   
    /**
     * @OA\Post(
     *     path="/api/v1/statuses",
     *     tags={"Statuses"},
     *     summary="Create new status",
     *     description="Create new status",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Status")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function store(StoreStatusRequest $request)
    {
        $status = $this->statusService->create($request->validated());
        $this->forgetCache(self::CACHE_KEY_STATUSES_LIST); // Очистка кэша после создания
        return $this->successResponse(new StatusResource($status), 201);
    }

    // Получение конкретного статуса   
    /**
     * @OA\Get(
     *     path="/api/v1/statuses/{id}",
     *     tags={"Statuses"},
     *     summary="Get status by ID",
     *     description="Get status by ID",
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
     *             @OA\Property(property="data", ref="#/components/schemas/Status")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_STATUS . $id;

        $status = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
            return $this->statusService->getById($id);
        });

        if ($status) {
            return $this->successResponse(new StatusResource($status));
        }

        return $this->errorResponse('Status not found', 404);
    }

    // Обновление статуса   
    /**
     * @OA\Put(
     *     path="/api/v1/statuses/{id}",
     *     tags={"Statuses"},
     *     summary="Update status",
     *     description="Update status",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Status")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function update(UpdateStatusRequest $request, $id)
    {
        $status = $this->statusService->update($id, $request->validated());

        if ($status) {
            $this->forgetCache([
                self::CACHE_KEY_STATUS . $id,
                self::CACHE_KEY_STATUSES_LIST
            ]);

            return $this->successResponse(new StatusResource($status));
        }

        return $this->errorResponse('Status not found', 404);
    }

    // Удаление статуса   
    /**
     * @OA\Delete(
     *     path="/api/v1/statuses/{id}",
     *     tags={"Statuses"},
     *     summary="Delete status",
     *     description="Delete status",
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
        $this->statusService->delete($id);
        $this->forgetCache(self::CACHE_KEY_STATUS . $id);
        $this->forgetCache(self::CACHE_KEY_STATUSES_LIST);

        return $this->successResponse(['message' => 'Status deleted successfully']);
    }
}
