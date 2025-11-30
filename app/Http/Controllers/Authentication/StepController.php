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
     *     path="/api/v1/auth/step/one",
     *     tags={"Steps"},
     *     summary="One step",
     *     description="One step",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StepOneRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Step")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function one(StepOneRequest $request)
    {
        try {
            $request->user()->sources()->syncWithoutDetaching($request->get('source_id'));
            
            Log::info('User onboarding step one completed', [
                'user_id' => Auth::id(),
                'source_id' => $request->get('source_id')
            ]);
            
            return $this->successResponse(['message' => 'Source successfully added']);
        } catch (Exception $e) {
            Log::error('Error completing onboarding step one: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'source_id' => $request->get('source_id')
            ]);
            
            return $this->errorResponse('Failed to complete step one. Please try again.', 500);
        }
    }

    // Шаг 2: Добавление навыков   
    /**
     * @OA\Post(
     *     path="/api/v1/auth/step/two",
     *     tags={"Steps"},
     *     summary="Two step",
     *     description="Two step",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StepTwoRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Step")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function two(StepTwoRequest $request)
    {
        try {
            $request->user()->skills()->syncWithoutDetaching($request->get('skill_ids'));
            
            Log::info('User onboarding step two completed', [
                'user_id' => Auth::id(),
                'skill_ids' => $request->get('skill_ids')
            ]);
            
            return $this->successResponse(['message' => 'Skills successfully added']);
        } catch (Exception $e) {
            Log::error('Error completing onboarding step two: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'skill_ids' => $request->get('skill_ids')
            ]);
            
            return $this->errorResponse('Failed to complete step two. Please try again.', 500);
        }
    }

    // Шаг 3: Загрузка аватара   
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
     *             @OA\Property(property="data", ref="#/components/schemas/Step")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
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
