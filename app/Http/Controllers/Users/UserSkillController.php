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
     * Display a listing of the skills.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $skills = $this->getFromCacheOrStore(self::CACHE_KEY_SKILLS_LIST, self::CACHE_MINUTES, function () {
                return $this->skillService->getAllSkills();
            });

            Log::info('Skills retrieved successfully');

            return $this->successResponse($skills);
        } catch (Exception $e) {
            Log::error('Error retrieving skills: '.$e->getMessage());

            return $this->errorResponse($e->getMessage(), 500);
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

            return $this->successResponse($skill);
        } catch (Exception $e) {
            Log::error('Error retrieving skill: '.$e->getMessage(), ['id' => $id]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreSkillRequest $request)
    {
        try {
            $skill = $this->skillService->storeSkill($request->all());
            Log::info('Skill stored successfully', ['skill_id' => $skill->id, 'data' => $request->all()]);

            $this->forgetCache(self::CACHE_KEY_SKILLS_LIST);

            return $this->successResponse(['message' => 'Skill stored successfully', 'skill' => $skill], 201);
        } catch (Exception $e) {
            Log::error('Error storing skill: '.$e->getMessage(), ['data' => $request->all()]);

            return $this->errorResponse($e->getMessage(), 500);
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

            $this->forgetCache(self::CACHE_KEY_USER_SKILLS . $user->id);

            return $this->successResponse(['message' => 'Skill added successfully']);
        } catch (Exception $e) {
            Log::error('Error adding skill to user: '.$e->getMessage(), ['user_id' => Auth::id(), 'skill_ids' => $request->input('skill_ids')]);

            return $this->errorResponse($e->getMessage(), 500);
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

            $this->forgetCache(self::CACHE_KEY_USER_SKILLS . $user->id);

            return $this->successResponse(['message' => 'Skill removed successfully']);
        } catch (Exception $e) {
            Log::error('Error removing skill from user: '.$e->getMessage(), ['user_id' => Auth::id(), 'skill_id' => $request->input('skill_id')]);

            return $this->errorResponse($e->getMessage(), 500);
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
            $cacheKey = self::CACHE_KEY_USER_SKILLS . $user->id;

            $skills = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
                return $this->skillService->getUserSkills($user->id);
            });

            Log::info('User skills retrieved successfully', ['user_id' => $user->id]);

            return $this->successResponse($skills);
        } catch (Exception $e) {
            Log::error('Error retrieving user skills: '.$e->getMessage(), ['user_id' => Auth::id()]);

            return $this->errorResponse($e->getMessage(), 500);
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
            $skill = $this->skillService->updateSkill($id, $request->all());
            Log::info('Skill updated successfully', ['skill_id' => $id, 'data' => $request->all()]);

            $this->forgetCache(self::CACHE_KEY_SKILLS_LIST);

            return $this->successResponse(['message' => 'Skill updated successfully', 'skill' => $skill]);
        } catch (Exception $e) {
            Log::error('Error updating skill: '.$e->getMessage(), ['skill_id' => $id, 'data' => $request->all()]);

            return $this->errorResponse($e->getMessage(), 500);
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
