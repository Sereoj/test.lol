<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ImageController extends Controller
{    /**
     * @OA\Get(
     *     path="/api/v1/storage/originals/{filename}",
     *     tags={"Images"},
     *     summary="GetOriginal image",
     *     description="GetOriginal image",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="Filename",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Image")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function getOriginal($filename)
    {
        $path = '/originals/' . $filename;

        if (Storage::exists($path)) {
            return response()->file(storage_path('app/' . $path));
        }
        return null;
    }
}
