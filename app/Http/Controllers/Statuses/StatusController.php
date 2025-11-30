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
     * @OA\Delete(
     *     path="/api/v1/statuses/{id}",
     *     tags={"Statuses"},
     *     summary="Delete status",
     *     description="Delete status",
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
        $this->statusService->delete($id);
        $this->forgetCache(self::CACHE_KEY_STATUS . $id);
        $this->forgetCache(self::CACHE_KEY_STATUSES_LIST);

        return $this->successResponse(['message' => 'Status deleted successfully']);
    }
}
