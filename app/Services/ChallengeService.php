<?php

namespace App\Services;

use App\Events\Challenges\ChallengeCreated;
use App\Events\Challenges\ChallengeCompleted;
use App\Events\Challenges\SubmissionCreated;
use App\Events\Challenges\VoteCasted;
use App\Events\Challenges\WinnerSelected;
use App\Models\Billing\Fee;
use App\Models\Challenge;
use App\Models\ChallengeWinner;
use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Models\Users\UserBalance;
use App\Repositories\ChallengeRepository;
use App\Repositories\ChallengePrizeRepository;
use App\Services\Billing\BalanceService;
use App\Services\Billing\TransactionService;
use App\Traits\LoggableTrait;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ChallengeService extends BaseService
{
    use LoggableTrait;

    protected ChallengeRepository $challengeRepository;
    protected ChallengePrizeRepository $prizeRepository;
    protected BalanceService $balanceService;
    protected TransactionService $transactionService;

    /**
     * ChallengeService constructor.
     *
     * @param ChallengeRepository $challengeRepository
     * @param ChallengePrizeRepository $prizeRepository
     * @param BalanceService $balanceService
     * @param TransactionService $transactionService
     */
    public function __construct(
        ChallengeRepository $challengeRepository,
        ChallengePrizeRepository $prizeRepository,
        BalanceService $balanceService,
        TransactionService $transactionService
    ) {
        $this->challengeRepository = $challengeRepository;
        $this->prizeRepository = $prizeRepository;
        $this->balanceService = $balanceService;
        $this->transactionService = $transactionService;
    }

    /**
     * Получить все челленджи.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getAll(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getAllChallenges($perPage, $filters);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Получить челлендж по ID.
     *
     * @param int $id
     * @return Challenge
     * @throws Exception
     */
    public function getById(int $id): Challenge
    {
        try {
            return $this->challengeRepository->getChallengeById($id);
        } catch (Exception $e) {
            throw new Exception('Челлендж не найден');
        }
    }

    /**
     * Создать новый челлендж.
     *
     * @param array $data
     * @return Challenge
     * @throws Exception
     */
    public function create(array $data): Challenge
    {
        try {
            return $this->challengeRepository->createChallenge($data);
        } catch (Exception $e) {
            throw new Exception('Ошибка при создании челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Обновить челлендж.
     *
     * @param int $id
     * @param array $data
     * @return Challenge
     * @throws Exception
     */
    public function update(int $id, array $data): Challenge
    {
        try {
            return $this->challengeRepository->updateChallenge($id, $data);
        } catch (Exception $e) {
            throw new Exception('Ошибка при обновлении челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Удалить челлендж.
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete($id): bool
    {
        try {
            return $this->challengeRepository->deleteChallenge($id);
        } catch (Exception $e) {
            throw new Exception('Ошибка при удалении челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Получить активные челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getActiveChallenges(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getActiveChallenges($perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении активных челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Получить предстоящие челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getUpcomingChallenges(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getUpcomingChallenges($perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении предстоящих челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Получить завершенные челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getCompletedChallenges(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getCompletedChallenges($perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении завершенных челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Добавить пользователя к челленджу.
     *
     * @param int $challengeId
     * @param int $userId
     * @throws Exception
     */
    public function addParticipant(int $challengeId, int $userId): void
    {
        try {
            $this->challengeRepository->addParticipant($challengeId, $userId);
        } catch (Exception $e) {
            throw new Exception('Ошибка при добавлении участника: ' . $e->getMessage());
        }
    }

    /**
     * Удалить пользователя из челленджа.
     *
     * @param int $challengeId
     * @param int $userId
     * @throws Exception
     */
    public function removeParticipant(int $challengeId, int $userId): void
    {
        try {
            $this->challengeRepository->removeParticipant($challengeId, $userId);
        } catch (Exception $e) {
            throw new Exception('Ошибка при удалении участника: ' . $e->getMessage());
        }
    }

    /**
     * Получить челленджи пользователя.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getUserChallenges(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getUserChallenges($userId, $perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении челленджей пользователя: ' . $e->getMessage());
        }
    }

    /**
     * Создать челлендж с финансовой логикой.
     *
     * @param array $data
     * @param int $organizerId
     * @return Challenge
     * @throws Exception
     */
    public function createChallenge(array $data, int $organizerId): Challenge
    {
        try {
            return DB::transaction(function () use ($data, $organizerId) {
                $this->logInfo('Начало создания челленджа', [
                    'organizer_id' => $organizerId,
                    'type' => $data['type'] ?? 'user',
                ]);

                $organizer = User::findOrFail($organizerId);

                if (($data['type'] ?? 'user') === 'official' && !$organizer->verification) {
                    $this->logWarning('Попытка создания официального челленджа неверифицированным пользователем', [
                        'organizer_id' => $organizerId,
                    ]);
                    throw new Exception('Только верифицированные пользователи могут создавать официальные челленджи');
                }

                if (!isset($data['prizes']) || !is_array($data['prizes'])) {
                    throw new Exception('Не указаны призовые места');
                }

                if (!$this->prizeRepository->validatePrizeDistribution($data['prizes'])) {
                    $this->logWarning('Неверное распределение призов', [
                        'organizer_id' => $organizerId,
                        'prizes' => $data['prizes'],
                    ]);
                    throw new Exception('Сумма процентов призов должна быть равна 100');
                }

                $prizeAmount = $data['prize_amount'];
                $currency = $data['prize_currency'] ?? 'RUB';

                $platformFeePercentage = $this->getDefaultPlatformFeePercentage();
                $platformFeeAmount = ($prizeAmount * $platformFeePercentage) / 100;
                $netPrizeAmount = $prizeAmount - $platformFeeAmount;

                $userBalance = UserBalance::where('user_id', $organizerId)
                    ->where('currency', $currency)
                    ->first();

                if (!$userBalance || $userBalance->balance < $prizeAmount) {
                    $this->logWarning('Недостаточно средств для создания челленджа', [
                        'organizer_id' => $organizerId,
                        'required_amount' => $prizeAmount,
                        'current_balance' => $userBalance->balance ?? 0,
                        'currency' => $currency,
                    ]);
                    throw new Exception('Недостаточно средств на балансе');
                }

                $this->freezeChallengeAmount($organizerId, $prizeAmount, $currency);

                $challengeData = array_merge($data, [
                    'organizer_id' => $organizerId,
                    'platform_fee_percentage' => $platformFeePercentage,
                    'platform_fee_amount' => $platformFeeAmount,
                    'net_prize_amount' => $netPrizeAmount,
                    'status' => 'pending_payment',
                ]);

                $challenge = $this->challengeRepository->createChallengeWithPrizes(
                    $challengeData,
                    $this->calculatePrizes($data['prizes'], $netPrizeAmount)
                );

                $challenge->update(['status' => 'draft']);

                $this->transactionService->createTransaction(
                    $organizerId,
                    'challenge_freeze',
                    -$prizeAmount,
                    $currency,
                    [
                        'challenge_id' => $challenge->id,
                        'platform_fee' => $platformFeeAmount,
                        'net_prize_amount' => $netPrizeAmount,
                    ]
                );

                event(new ChallengeCreated($challenge));

                $this->logInfo('Челлендж успешно создан', [
                    'challenge_id' => $challenge->id,
                    'organizer_id' => $organizerId,
                    'prize_amount' => $prizeAmount,
                    'currency' => $currency,
                ]);

                return $challenge->load(['organizer', 'prizes']);
            });
        } catch (Exception $e) {
            $this->logError('Ошибка при создании челленджа', [
                'organizer_id' => $organizerId,
            ], $e);
            throw new Exception('Ошибка при создании челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Подать работу на челлендж.
     *
     * @param int $challengeId
     * @param int $userId
     * @param int $postId
     * @return void
     * @throws Exception
     */
    public function submitWork(int $challengeId, int $userId, int $postId): void
    {
        try {
            DB::transaction(function () use ($challengeId, $userId, $postId) {
                $this->logInfo('Подача работы на челлендж', [
                    'challenge_id' => $challengeId,
                    'user_id' => $userId,
                    'post_id' => $postId,
                ]);

                $challenge = $this->challengeRepository->getChallengeById($challengeId);

                if (!$challenge->canSubmit()) {
                    $this->logWarning('Попытка подачи работы на неактивный челлендж', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                        'status' => $challenge->status,
                    ]);
                    throw new Exception('Прием работ завершен или челлендж неактивен');
                }

                $isParticipant = $challenge->participants()->where('user_id', $userId)->exists();
                if (!$isParticipant) {
                    $this->logWarning('Пользователь не является участником челленджа', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                    ]);
                    throw new Exception('Вы не являетесь участником этого челленджа');
                }

                $post = Post::findOrFail($postId);

                if ($post->user_id !== $userId) {
                    $this->logWarning('Попытка подать чужой пост', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                        'post_id' => $postId,
                        'post_owner_id' => $post->user_id,
                    ]);
                    throw new Exception('Вы можете подавать только свои работы');
                }

                if ($post->challenge_id && $post->challenge_id !== $challengeId) {
                    $this->logWarning('Пост уже участвует в другом челлендже', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                        'post_id' => $postId,
                        'existing_challenge_id' => $post->challenge_id,
                    ]);
                    throw new Exception('Эта работа уже участвует в другом челлендже');
                }

                $post->update(['challenge_id' => $challengeId]);

                $this->challengeRepository->markAsSubmitted($challengeId, $userId);

                $this->challengeRepository->updateChallengeCounters($challengeId);

                event(new SubmissionCreated($challenge, $post));

                $this->logInfo('Работа успешно подана', [
                    'challenge_id' => $challengeId,
                    'user_id' => $userId,
                    'post_id' => $postId,
                ]);
            });
        } catch (Exception $e) {
            $this->logError('Ошибка при подаче работы', [
                'challenge_id' => $challengeId,
                'user_id' => $userId,
                'post_id' => $postId,
            ], $e);
            throw new Exception('Ошибка при подаче работы: ' . $e->getMessage());
        }
    }

    /**
     * Проголосовать за работу.
     *
     * @param int $challengeId
     * @param int $userId
     * @param int $postId
     * @return void
     * @throws Exception
     */
    public function vote(int $challengeId, int $userId, int $postId): void
    {
        try {
            DB::transaction(function () use ($challengeId, $userId, $postId) {
                $this->logInfo('Голосование за работу', [
                    'challenge_id' => $challengeId,
                    'user_id' => $userId,
                    'post_id' => $postId,
                ]);

                $challenge = $this->challengeRepository->getChallengeById($challengeId);

                if (!$challenge->isVotingOpen()) {
                    $this->logWarning('Попытка голосования при закрытом голосовании', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                        'status' => $challenge->status,
                    ]);
                    throw new Exception('Голосование не открыто');
                }

                if (!$challenge->canUserVote($userId)) {
                    $this->logWarning('Пользователь не может голосовать', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                        'selection_method' => $challenge->winner_selection_method,
                    ]);
                    throw new Exception('У вас нет прав для голосования в этом челлендже');
                }

                if ($challenge->hasUserVoted($userId)) {
                    $this->logWarning('Попытка повторного голосования', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                    ]);
                    throw new Exception('Вы уже проголосовали в этом челлендже');
                }

                $post = Post::findOrFail($postId);

                if ($post->challenge_id !== $challengeId) {
                    $this->logWarning('Пост не участвует в челлендже', [
                        'challenge_id' => $challengeId,
                        'post_id' => $postId,
                    ]);
                    throw new Exception('Эта работа не участвует в данном челлендже');
                }

                if ($post->user_id === $userId) {
                    $this->logWarning('Попытка проголосовать за свою работу', [
                        'challenge_id' => $challengeId,
                        'user_id' => $userId,
                        'post_id' => $postId,
                    ]);
                    throw new Exception('Нельзя голосовать за свою работу');
                }

                $this->challengeRepository->addVote($challengeId, $userId, $postId);

                $this->challengeRepository->updateChallengeCounters($challengeId);

                event(new VoteCasted($challenge, $userId, $post));

                $this->logInfo('Голос успешно учтен', [
                    'challenge_id' => $challengeId,
                    'user_id' => $userId,
                    'post_id' => $postId,
                ]);
            });
        } catch (Exception $e) {
            $this->logError('Ошибка при голосовании', [
                'challenge_id' => $challengeId,
                'user_id' => $userId,
                'post_id' => $postId,
            ], $e);
            throw new Exception('Ошибка при голосовании: ' . $e->getMessage());
        }
    }

    /**
     * Выбрать победителей вручную (manual selection).
     *
     * @param int $challengeId
     * @param int $organizerId
     * @param array $winners
     * @return void
     * @throws Exception
     */
    public function selectWinners(int $challengeId, int $organizerId, array $winners): void
    {
        try {
            DB::transaction(function () use ($challengeId, $organizerId, $winners) {
                $this->logInfo('Выбор победителей челленджа', [
                    'challenge_id' => $challengeId,
                    'organizer_id' => $organizerId,
                    'winners_count' => count($winners),
                ]);

                $challenge = $this->challengeRepository->getChallengeById($challengeId);

                if (!$challenge->isOrganizer($organizerId)) {
                    $this->logWarning('Попытка выбрать победителей не организатором', [
                        'challenge_id' => $challengeId,
                        'user_id' => $organizerId,
                        'organizer_id' => $challenge->organizer_id,
                    ]);
                    throw new Exception('Только организатор может выбирать победителей');
                }

                if ($challenge->winner_selection_method !== 'manual') {
                    $this->logWarning('Попытка ручного выбора при автоматическом методе', [
                        'challenge_id' => $challengeId,
                        'selection_method' => $challenge->winner_selection_method,
                    ]);
                    throw new Exception('Этот челлендж использует автоматический выбор победителей');
                }

                if (!in_array($challenge->status, ['active', 'selecting_winners'])) {
                    $this->logWarning('Попытка выбрать победителей при неверном статусе', [
                        'challenge_id' => $challengeId,
                        'status' => $challenge->status,
                    ]);
                    throw new Exception('Невозможно выбрать победителей в текущем статусе челленджа');
                }

                $prizes = $challenge->prizes()->orderBy('place')->get();

                if (count($winners) !== $prizes->count()) {
                    throw new Exception('Количество победителей должно соответствовать количеству призовых мест');
                }

                foreach ($winners as $winner) {
                    $post = Post::findOrFail($winner['post_id']);

                    if ($post->challenge_id !== $challengeId) {
                        throw new Exception('Работа не участвует в этом челлендже');
                    }

                    $prize = $prizes->firstWhere('place', $winner['place']);
                    if (!$prize) {
                        throw new Exception("Призовое место {$winner['place']} не найдено");
                    }

                    $winnerData = [
                        'challenge_id' => $challengeId,
                        'user_id' => $post->user_id,
                        'post_id' => $post->id,
                        'place' => $winner['place'],
                        'prize_amount' => $prize->amount,
                        'prize_currency' => $challenge->prize_currency,
                        'payout_status' => 'pending',
                    ];

                    $createdWinner = $this->challengeRepository->addWinner($winnerData);

                    $this->payoutPrize($createdWinner);

                    event(new WinnerSelected($challenge, $createdWinner));
                }

                $this->transferPlatformFee($challenge);

                $challenge->update([
                    'status' => 'completed',
                    'results_announced_at' => now(),
                ]);

                event(new ChallengeCompleted($challenge));

                $this->logInfo('Победители успешно выбраны и награждены', [
                    'challenge_id' => $challengeId,
                    'winners_count' => count($winners),
                ]);
            });
        } catch (Exception $e) {
            $this->logError('Ошибка при выборе победителей', [
                'challenge_id' => $challengeId,
                'organizer_id' => $organizerId,
            ], $e);
            throw new Exception('Ошибка при выборе победителей: ' . $e->getMessage());
        }
    }

    /**
     * Завершить челлендж с автоматическим определением победителей (voting).
     *
     * @param int $challengeId
     * @return void
     * @throws Exception
     */
    public function finishChallenge(int $challengeId): void
    {
        try {
            DB::transaction(function () use ($challengeId) {
                $this->logInfo('Завершение челленджа с голосованием', [
                    'challenge_id' => $challengeId,
                ]);

                $challenge = $this->challengeRepository->getChallengeById($challengeId);

                if ($challenge->winner_selection_method === 'manual') {
                    $this->logWarning('Попытка автоматического завершения при ручном выборе', [
                        'challenge_id' => $challengeId,
                    ]);
                    throw new Exception('Этот челлендж требует ручного выбора победителей');
                }

                if ($challenge->status !== 'voting') {
                    $this->logWarning('Попытка завершения при неверном статусе', [
                        'challenge_id' => $challengeId,
                        'status' => $challenge->status,
                    ]);
                    throw new Exception('Челлендж не находится в стадии голосования');
                }

                $prizes = $challenge->prizes()->orderBy('place')->get();
                $prizesCount = $prizes->count();

                $topSubmissions = $this->challengeRepository->getTopSubmissionsByVotes($challengeId, $prizesCount);

                if ($topSubmissions->count() < $prizesCount) {
                    $this->logWarning('Недостаточно работ для всех призовых мест', [
                        'challenge_id' => $challengeId,
                        'required' => $prizesCount,
                        'actual' => $topSubmissions->count(),
                    ]);
                    throw new Exception('Недостаточно работ для определения всех победителей');
                }

                foreach ($topSubmissions as $index => $submission) {
                    $place = $index + 1;
                    $prize = $prizes->firstWhere('place', $place);

                    $post = Post::find($submission->post_id);

                    $winnerData = [
                        'challenge_id' => $challengeId,
                        'user_id' => $post->user_id,
                        'post_id' => $post->id,
                        'place' => $place,
                        'prize_amount' => $prize->amount,
                        'prize_currency' => $challenge->prize_currency,
                        'payout_status' => 'pending',
                    ];

                    $winner = $this->challengeRepository->addWinner($winnerData);

                    $this->payoutPrize($winner);

                    event(new WinnerSelected($challenge, $winner));
                }

                $this->transferPlatformFee($challenge);

                $challenge->update([
                    'status' => 'completed',
                    'results_announced_at' => now(),
                ]);

                event(new ChallengeCompleted($challenge));

                $this->logInfo('Челлендж успешно завершен', [
                    'challenge_id' => $challengeId,
                    'winners_count' => $topSubmissions->count(),
                ]);
            });
        } catch (Exception $e) {
            $this->logError('Ошибка при завершении челленджа', [
                'challenge_id' => $challengeId,
            ], $e);
            throw new Exception('Ошибка при завершении челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Отменить челлендж с возвратом средств.
     *
     * @param int $challengeId
     * @param int $organizerId
     * @return void
     * @throws Exception
     */
    public function cancelChallenge(int $challengeId, int $organizerId): void
    {
        try {
            DB::transaction(function () use ($challengeId, $organizerId) {
                $this->logInfo('Отмена челленджа', [
                    'challenge_id' => $challengeId,
                    'organizer_id' => $organizerId,
                ]);

                $challenge = $this->challengeRepository->getChallengeById($challengeId);

                if (!$challenge->isOrganizer($organizerId)) {
                    $this->logWarning('Попытка отмены челленджа не организатором', [
                        'challenge_id' => $challengeId,
                        'user_id' => $organizerId,
                        'organizer_id' => $challenge->organizer_id,
                    ]);
                    throw new Exception('Только организатор может отменить челлендж');
                }

                if (in_array($challenge->status, ['completed', 'cancelled'])) {
                    $this->logWarning('Попытка отмены завершенного или уже отмененного челленджа', [
                        'challenge_id' => $challengeId,
                        'status' => $challenge->status,
                    ]);
                    throw new Exception('Невозможно отменить завершенный или уже отмененный челлендж');
                }

                if ($challenge->status === 'voting' || $challenge->status === 'selecting_winners') {
                    $this->logWarning('Попытка отмены челленджа на поздней стадии', [
                        'challenge_id' => $challengeId,
                        'status' => $challenge->status,
                    ]);
                    throw new Exception('Невозможно отменить челлендж на этой стадии');
                }

                $userBalance = UserBalance::where('user_id', $organizerId)
                    ->where('currency', $challenge->prize_currency)
                    ->first();

                if (!$userBalance) {
                    throw new Exception('Баланс организатора не найден');
                }

                $userBalance->balance += $challenge->prize_amount;
                $userBalance->save();

                $this->transactionService->createTransaction(
                    $organizerId,
                    'challenge_refund',
                    $challenge->prize_amount,
                    $challenge->prize_currency,
                    [
                        'challenge_id' => $challenge->id,
                        'reason' => 'Отмена челленджа организатором',
                    ]
                );

                $challenge->update(['status' => 'cancelled']);

                $this->logInfo('Челлендж успешно отменен, средства возвращены', [
                    'challenge_id' => $challengeId,
                    'organizer_id' => $organizerId,
                    'refunded_amount' => $challenge->prize_amount,
                    'currency' => $challenge->prize_currency,
                ]);
            });
        } catch (Exception $e) {
            $this->logError('Ошибка при отмене челленджа', [
                'challenge_id' => $challengeId,
                'organizer_id' => $organizerId,
            ], $e);
            throw new Exception('Ошибка при отмене челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Заморозить средства для челленджа (списание с balance).
     *
     * @param int $userId
     * @param float $amount
     * @param string $currency
     * @return void
     * @throws Exception
     */
    private function freezeChallengeAmount(int $userId, float $amount, string $currency): void
    {
        $userBalance = UserBalance::where('user_id', $userId)
            ->where('currency', $currency)
            ->first();

        if (!$userBalance || $userBalance->balance < $amount) {
            throw new Exception('Недостаточно средств на балансе');
        }

        $userBalance->balance -= $amount;
        $userBalance->save();

        $this->logInfo('Средства заморожены для челленджа', [
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => $currency,
            'remaining_balance' => $userBalance->balance,
        ]);
    }

    /**
     * Выплатить приз победителю.
     *
     * @param ChallengeWinner $winner
     * @return void
     * @throws Exception
     */
    private function payoutPrize(ChallengeWinner $winner): void
    {
        try {
            $userBalance = UserBalance::where('user_id', $winner->user_id)
                ->where('currency', $winner->prize_currency)
                ->first();

            if (!$userBalance) {
                throw new Exception("Баланс победителя не найден для валюты {$winner->prize_currency}");
            }

            $userBalance->balance += $winner->prize_amount;
            $userBalance->save();

            $transaction = $this->transactionService->createTransaction(
                $winner->user_id,
                'challenge_prize',
                $winner->prize_amount,
                $winner->prize_currency,
                [
                    'challenge_id' => $winner->challenge_id,
                    'place' => $winner->place,
                    'winner_id' => $winner->id,
                ]
            );

            $winner->update([
                'payout_status' => 'completed',
                'transaction_id' => $transaction->id,
                'payout_completed_at' => now(),
            ]);

            $this->logInfo('Приз выплачен победителю', [
                'user_id' => $winner->user_id,
                'challenge_id' => $winner->challenge_id,
                'place' => $winner->place,
                'amount' => $winner->prize_amount,
                'currency' => $winner->prize_currency,
                'transaction_id' => $transaction->id,
            ]);
        } catch (Exception $e) {
            $winner->update(['payout_status' => 'failed']);
            $this->logError('Ошибка при выплате приза', [
                'winner_id' => $winner->id,
                'user_id' => $winner->user_id,
            ], $e);
            throw $e;
        }
    }

    /**
     * Учесть комиссию платформы.
     *
     * @param Challenge $challenge
     * @return void
     */
    private function transferPlatformFee(Challenge $challenge): void
    {
        $this->logInfo('Комиссия платформы учтена', [
            'challenge_id' => $challenge->id,
            'fee_amount' => $challenge->platform_fee_amount,
            'fee_percentage' => $challenge->platform_fee_percentage,
            'currency' => $challenge->prize_currency,
        ]);
    }

    /**
     * Рассчитать суммы призов на основе процентов.
     *
     * @param array $prizes
     * @param float $netPrizeAmount
     * @return array
     */
    private function calculatePrizes(array $prizes, float $netPrizeAmount): array
    {
        $calculated = [];

        foreach ($prizes as $prize) {
            $calculated[] = [
                'place' => $prize['place'],
                'percentage' => $prize['percentage'],
                'amount' => ($netPrizeAmount * $prize['percentage']) / 100,
            ];
        }

        return $calculated;
    }

    /**
     * Получить процент комиссии платформы по умолчанию.
     *
     * @return float
     */
    private function getDefaultPlatformFeePercentage(): float
    {
        $fee = Fee::getFeeByType('platform', 'challenge');

        if ($fee && $fee->percentage) {
            return $fee->percentage;
        }

        return 10.00;
    }
}