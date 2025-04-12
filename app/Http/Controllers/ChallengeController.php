<?php

namespace App\Http\Controllers;

use App\Http\Requests\Challenge\ChallengeRequest;
use App\Http\Requests\Challenge\ParticipateRequest;
use App\Http\Resources\ChallengeResource;
use App\Services\ChallengeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChallengeController extends Controller
{
    protected ChallengeService $challengeService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_CHALLENGES = 'challenges';
    private const CACHE_KEY_CHALLENGE = 'challenge_';
    private const CACHE_KEY_ACTIVE_CHALLENGES = 'active_challenges';
    private const CACHE_KEY_USER_CHALLENGES = 'user_challenges_';

    /**
     * ChallengeController constructor.
     *
     * @param ChallengeService $challengeService
     */
    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
    }

    /**
     * Получить список челленджей.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $filters = $request->only(['status', 'search']);

            $cacheKey = self::CACHE_KEY_CHALLENGES . '_' . md5(json_encode($filters) . $perPage);
            
            $challenges = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($perPage, $filters) {
                return $this->challengeService->getAll($perPage, $filters);
            });

            Log::info('Челленджи успешно получены', ['filters' => $filters]);
            
            return $this->successResponse(ChallengeResource::collection($challenges));
        } catch (Exception $e) {
            Log::error('Ошибка при получении челленджей: ' . $e->getMessage());
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Создать новый челлендж.
     *
     * @param ChallengeRequest $request
     * @return JsonResponse
     */
    public function store(ChallengeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $challenge = $this->challengeService->create($data);

            $this->forgetCache(self::CACHE_KEY_CHALLENGES);
            $this->forgetCache(self::CACHE_KEY_ACTIVE_CHALLENGES);
            
            Log::info('Челлендж успешно создан', ['id' => $challenge->id]);
            
            return $this->successResponse(new ChallengeResource($challenge), 201);
        } catch (Exception $e) {
            Log::error('Ошибка при создании челленджа: ' . $e->getMessage());
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить детали челленджа.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cacheKey = self::CACHE_KEY_CHALLENGE . $id;
            
            $challenge = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->challengeService->getById($id);
            });
            
            Log::info('Челлендж успешно получен', ['id' => $id]);
            
            return $this->successResponse(new ChallengeResource($challenge));
        } catch (Exception $e) {
            Log::error('Ошибка при получении челленджа: ' . $e->getMessage(), ['id' => $id]);
            
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Обновить челлендж.
     *
     * @param ChallengeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ChallengeRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $challenge = $this->challengeService->update($id, $data);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);
            $this->forgetCache(self::CACHE_KEY_CHALLENGES);
            $this->forgetCache(self::CACHE_KEY_ACTIVE_CHALLENGES);
            
            Log::info('Челлендж успешно обновлен', ['id' => $id]);
            
            return $this->successResponse(new ChallengeResource($challenge));
        } catch (Exception $e) {
            Log::error('Ошибка при обновлении челленджа: ' . $e->getMessage(), ['id' => $id]);
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Удалить челлендж.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->challengeService->delete($id);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);
            $this->forgetCache(self::CACHE_KEY_CHALLENGES);
            $this->forgetCache(self::CACHE_KEY_ACTIVE_CHALLENGES);
            $this->forgetCache(self::CACHE_KEY_USER_CHALLENGES);
            
            Log::info('Челлендж успешно удален', ['id' => $id]);
            
            return $this->successResponse(null, 204);
        } catch (Exception $e) {
            Log::error('Ошибка при удалении челленджа: ' . $e->getMessage(), ['id' => $id]);
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить активные челленджи.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveChallenges(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $cacheKey = self::CACHE_KEY_ACTIVE_CHALLENGES . $perPage;
            
            $challenges = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($perPage) {
                return $this->challengeService->getActiveChallenges($perPage);
            });
            
            Log::info('Активные челленджи успешно получены');
            
            return $this->successResponse(ChallengeResource::collection($challenges));
        } catch (Exception $e) {
            Log::error('Ошибка при получении активных челленджей: ' . $e->getMessage());
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить челленджи пользователя.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserChallenges(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_USER_CHALLENGES . $userId . '_' . $perPage;
            
            $challenges = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId, $perPage) {
                return $this->challengeService->getUserChallenges($userId, $perPage);
            });
            
            Log::info('Челленджи пользователя успешно получены', ['user_id' => $userId]);
            
            return $this->successResponse(ChallengeResource::collection($challenges));
        } catch (Exception $e) {
            Log::error('Ошибка при получении челленджей пользователя: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Присоединиться к челленджу.
     *
     * @param ParticipateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function join(ParticipateRequest $request, int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->challengeService->addParticipant($id, $userId);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);
            $this->forgetCache(self::CACHE_KEY_USER_CHALLENGES . $userId . '_' . $request->get('per_page', 10));
            
            Log::info('Пользователь присоединился к челленджу', ['user_id' => $userId, 'challenge_id' => $id]);
            
            return $this->successResponse(['message' => 'Вы успешно присоединились к челленджу']);
        } catch (Exception $e) {
            Log::error('Ошибка при присоединении к челленджу: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'challenge_id' => $id
            ]);
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Покинуть челлендж.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function leave(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->challengeService->removeParticipant($id, $userId);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);
            $this->forgetCache(self::CACHE_KEY_USER_CHALLENGES . $userId . '_10');
            
            Log::info('Пользователь покинул челлендж', ['user_id' => $userId, 'challenge_id' => $id]);
            
            return $this->successResponse(['message' => 'Вы успешно покинули челлендж']);
        } catch (Exception $e) {
            Log::error('Ошибка при выходе из челленджа: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'challenge_id' => $id
            ]);
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
