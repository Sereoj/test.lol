<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Resources\LocationResource;
use App\Services\Locations\LocationService;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с местоположениями пользователей
class UserLocationController extends Controller
{
    protected LocationService $locationService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_LOCATIONS_ALL = 'locations_all';
    private const CACHE_KEY_LOCATION = 'location_';

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

                /**
     * @OA\Delete(
     *     path="/api/v1/locations/{id}",
     *     tags={"Users"},
     *     summary="Delete user location",
     *     description="Delete user location",
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
public function destroy(int $id)
    {
        try {
            $this->locationService->deleteLocation($id);
            Log::info('Location deleted successfully', ['id' => $id]);

            $this->forgetCache([
                self::CACHE_KEY_LOCATION . $id,
                self::CACHE_KEY_LOCATIONS_ALL
            ]);

            return $this->successResponse(['message' => 'Location deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting location: ' . $e->getMessage(), ['id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
