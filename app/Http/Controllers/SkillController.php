<?php

namespace App\Http\Controllers;

use App\Http\Requests\Skill\AddSkillRequest;
use App\Http\Requests\Skill\RemoveSkillRequest;
use App\Http\Requests\Skill\StoreSkillRequest;
use App\Http\Requests\Skill\UpdateSkillRequest;
use App\Services\SkillService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SkillController extends Controller
{
    protected SkillService $skillService;

    public function __construct(SkillService $skillService)
    {
        $this->skillService = $skillService;
    }

    /**
     * Display a listing of the skills.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Используем кеш для получения всех навыков
            $skills = Cache::remember('skills_list', now()->addMinutes(10), function () {
                return $this->skillService->getAllSkills();
            });

            Log::info('Skills retrieved successfully');

            return response()->json($skills, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving skills: '.$e->getMessage());

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified skill.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $skill = $this->skillService->getSkillById($id);
            Log::info('Skill retrieved successfully', ['id' => $id]);

            return response()->json($skill, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving skill: '.$e->getMessage(), ['id' => $id]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreSkillRequest $request)
    {
        try {
            $skill = $this->skillService->storeSkill($request->all());
            Log::info('Skill stored successfully', ['skill_id' => $skill->id, 'data' => $request->all()]);

            // После добавления нового навыка сбрасываем кеш
            Cache::forget('skills_list');

            return response()->json(['message' => 'Skill stored successfully', 'skill' => $skill], 201);
        } catch (Exception $e) {
            Log::error('Error storing skill: '.$e->getMessage(), ['data' => $request->all()]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Add a skill to the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSkill(AddSkillRequest $request)
    {
        try {
            $user = Auth::user();
            $this->skillService->addSkillToUser($user->id, $request->input('skill_ids'));
            Log::info('Skill added to user', ['user_id' => $user->id, 'skill_ids' => $request->input('skill_ids')]);

            return response()->json(['message' => 'Skill added successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error adding skill to user: '.$e->getMessage(), ['user_id' => $user->id, 'skill_ids' => $request->input('skill_ids')]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove a skill from the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeSkill(RemoveSkillRequest $request)
    {
        try {
            $user = Auth::user();
            $this->skillService->removeSkillFromUser($user->id, $request->input('skill_id'));
            Log::info('Skill removed from user', ['user_id' => $user->id, 'skill_id' => $request->input('skill_id')]);

            return response()->json(['message' => 'Skill removed successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error removing skill from user: '.$e->getMessage(), ['user_id' => $user->id, 'skill_id' => $request->input('skill_id')]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all skills for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserSkills()
    {
        try {
            $user = Auth::user();

            // Используем кеш для получения навыков пользователя
            $skills = Cache::remember("user_skills_{$user->id}", now()->addMinutes(10), function () use ($user) {
                return $this->skillService->getUserSkills($user->id);
            });

            Log::info('User skills retrieved successfully', ['user_id' => $user->id]);

            return response()->json($skills, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving user skills: '.$e->getMessage(), ['user_id' => $user->id]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified skill.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSkillRequest $request, int $id)
    {
        try {
            $this->skillService->updateSkill($id, $request->all());
            Log::info('Skill updated successfully', ['id' => $id, 'data' => $request->all()]);

            // После обновления навыка сбрасываем кеш с навыками
            Cache::forget('skills_list');

            return response()->json(['message' => 'Skill updated successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error updating skill: '.$e->getMessage(), ['id' => $id, 'data' => $request->all()]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified skill.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->skillService->deleteSkill($id);
            Log::info('Skill deleted successfully', ['id' => $id]);

            // После удаления навыка сбрасываем кеш с навыками
            Cache::forget('skills_list');

            return response()->json(['message' => 'Skill deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting skill: '.$e->getMessage(), ['id' => $id]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
