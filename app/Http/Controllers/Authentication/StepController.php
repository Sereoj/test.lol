<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Media\AvatarController;
use App\Http\Requests\Avatar\UploadAvatarRequest;
use App\Http\Requests\Step\StepOneRequest;
use App\Http\Requests\Step\StepTwoRequest;
use App\Models\Users\User;
use App\Services\Media\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

class StepController extends Controller
{
    protected AvatarService $avatarService;
    private AvatarController $avatarController;

    public function __construct(AvatarService $avatarService, AvatarController $avatarController)
    {
        $this->avatarService = $avatarService;
        $this->avatarController = $avatarController;
    }

    // Шаг 1: Добавление источников   
    
    /**
     * @OA\Post(
     *     path="/api/v1/auth/step/three",
     *     tags={"Steps"},
     *     summary="Three step",
     *     description="Three step",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UploadAvatarRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
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
public function three(UploadAvatarRequest $request)
    {
        try {
            Log::info('Initiating onboarding step three (avatar upload)', [
                'user_id' => Auth::id()
            ]);
            
            return $this->avatarController->uploadAvatar($request);
        } catch (Exception $e) {
            Log::error('Error in onboarding step three: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            
            return $this->errorResponse('Failed to upload avatar. Please try again.', 500);
        }
    }
}
