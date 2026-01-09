<?php

namespace App\Http\Controllers;

use App\Http\Requests\Challenge\ChallengeRequest;
use App\Http\Requests\Challenge\ParticipateRequest;
use App\Http\Requests\Challenge\SelectWinnersRequest;
use App\Http\Requests\Challenge\SubmitWorkRequest;
use App\Http\Requests\Challenge\VoteRequest;
use App\Http\Resources\ChallengeResource;
use App\Http\Resources\ChallengeWinnerResource;
use App\Models\Posts\Post;
use App\Services\ChallengeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Контроллер для работы с челленджами
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
            $userId = Auth::id();
            $challenge = $this->challengeService->createChallenge($data, $userId);

            $this->forgetCache(self::CACHE_KEY_CHALLENGES);
            $this->forgetCache(self::CACHE_KEY_ACTIVE_CHALLENGES);

            Log::info('Челлендж успешно создан', ['id' => $challenge->id]);

            return $this->successResponse(new ChallengeResource($challenge),[], 201);
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

    /**
     * Подать работу на челлендж.
     *
     * @param SubmitWorkRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function submitWork(SubmitWorkRequest $request, int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $postId = $request->validated()['post_id'];

            $this->challengeService->submitWork($id, $userId, $postId);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);

            Log::info('Работа успешно подана', [
                'user_id' => $userId,
                'challenge_id' => $id,
                'post_id' => $postId
            ]);

            return $this->successResponse(['message' => 'Работа успешно подана на челлендж']);
        } catch (Exception $e) {
            Log::error('Ошибка при подаче работы: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'challenge_id' => $id
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Проголосовать за работу.
     *
     * @param VoteRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function vote(VoteRequest $request, int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $postId = $request->validated()['post_id'];

            $this->challengeService->vote($id, $userId, $postId);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);

            Log::info('Голос успешно учтен', [
                'user_id' => $userId,
                'challenge_id' => $id,
                'post_id' => $postId
            ]);

            return $this->successResponse(['message' => 'Голос успешно учтен']);
        } catch (Exception $e) {
            Log::error('Ошибка при голосовании: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'challenge_id' => $id
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Выбрать победителей (manual).
     *
     * @param SelectWinnersRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function selectWinners(SelectWinnersRequest $request, int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $winners = $request->validated()['winners'];

            $this->challengeService->selectWinners($id, $userId, $winners);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);
            $this->forgetCache(self::CACHE_KEY_CHALLENGES);

            Log::info('Победители успешно выбраны', [
                'organizer_id' => $userId,
                'challenge_id' => $id,
                'winners_count' => count($winners)
            ]);

            return $this->successResponse(['message' => 'Победители успешно выбраны и награждены']);
        } catch (Exception $e) {
            Log::error('Ошибка при выборе победителей: ' . $e->getMessage(), [
                'organizer_id' => Auth::id(),
                'challenge_id' => $id
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить список работ челленджа.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function submissions(Request $request, int $id): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);

            $submissions = Post::where('challenge_id', $id)
                ->with(['user', 'media'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            Log::info('Работы челленджа успешно получены', ['challenge_id' => $id]);

            return $this->successResponse($submissions);
        } catch (Exception $e) {
            Log::error('Ошибка при получении работ челленджа: ' . $e->getMessage(), [
                'challenge_id' => $id
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить список победителей челленджа.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function winners(int $id): JsonResponse
    {
        try {
            $challenge = $this->challengeService->getById($id);
            $winners = $challenge->winners()->with(['user', 'post'])->orderBy('place')->get();

            Log::info('Победители челленджа успешно получены', ['challenge_id' => $id]);

            return $this->successResponse(ChallengeWinnerResource::collection($winners));
        } catch (Exception $e) {
            Log::error('Ошибка при получении победителей челленджа: ' . $e->getMessage(), [
                'challenge_id' => $id
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Отменить челлендж с возвратом средств.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();

            $this->challengeService->cancelChallenge($id, $userId);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);
            $this->forgetCache(self::CACHE_KEY_CHALLENGES);
            $this->forgetCache(self::CACHE_KEY_ACTIVE_CHALLENGES);

            Log::info('Челлендж успешно отменен', [
                'organizer_id' => $userId,
                'challenge_id' => $id
            ]);

            return $this->successResponse(['message' => 'Челлендж успешно отменен, средства возвращены']);
        } catch (Exception $e) {
            Log::error('Ошибка при отмене челленджа: ' . $e->getMessage(), [
                'organizer_id' => Auth::id(),
                'challenge_id' => $id
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
