<?php

namespace App\Http\Controllers;

use App\Http\Requests\Source\CreateSourceRequest;
use App\Http\Requests\Source\UpdateSourceRequest;
use App\Services\Content\SourceService;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с источниками
class SourceController extends Controller
{
    protected SourceService $sourceService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_SOURCES = 'sources';
    private const CACHE_KEY_SOURCE = 'source_';

    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }

                                    /**
     * @OA\Delete(
     *     path="/api/v1/sources/{id}",
     *     tags={"Sources"},
     *     summary="Delete source",
     *     description="Delete source",
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
public function destroy(int $id)
    {
        try {
            $this->sourceService->delete($id);

            $this->forgetCache([
                self::CACHE_KEY_SOURCE . $id,
                self::CACHE_KEY_SOURCES
            ]);

            Log::info('Source deleted successfully', ['id' => $id]);

            return $this->successResponse(['message' => 'Source deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting source: '.$e->getMessage(), ['id' => $id]);

            return $this->errorResponse('Failed to delete source. Please try again later.', 500);
        }
    }
}
