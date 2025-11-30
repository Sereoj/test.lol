<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Skill\AddSkillRequest;
use App\Http\Requests\Skill\RemoveSkillRequest;
use App\Http\Requests\Skill\StoreSkillRequest;
use App\Http\Requests\Skill\UpdateSkillRequest;
use App\Services\Content\SkillService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с навыками пользователей
class UserSkillController extends Controller
{
    protected SkillService $skillService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_SKILLS_LIST = 'skills_list';
    private const CACHE_KEY_USER_SKILLS = 'user_skills_';

    public function __construct(SkillService $skillService)
    {
        $this->skillService = $skillService;
    }

                                /**
     * @OA\Delete(
     *     path="/api/v1/skills/{id}",
     *     tags={"Users"},
     *     summary="Delete user skill",
     *     description="Delete user skill",
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
            $this->skillService->delete($id);
            Log::info('Skill deleted successfully', ['skill_id' => $id]);

            $this->forgetCache(self::CACHE_KEY_SKILLS_LIST);

            return $this->successResponse(['message' => 'Skill deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting skill: '.$e->getMessage(), ['skill_id' => $id]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
