<?php

namespace App\Services\Users;

use App\Events\Users\UserCreated;
use App\Events\Users\UserDeleted;
use App\Events\Users\UserUpdated;
use App\Models\Users\User;
use App\Repositories\Users\UserRepository;
use App\Services\RepositoryBasedService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Exception;

/**
 * Сервис для работы с пользователями
 */
class UserService extends RepositoryBasedService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'users';

    /**
     * Длительность кеширования (минуты)
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Класс события создания
     *
     * @var string
     */
    protected ?string $createEventClass = UserCreated::class;

    /**
     * Класс события обновления
     *
     * @var string
     */
    protected ?string $updateEventClass = UserUpdated::class;

    /**
     * Класс события удаления
     *
     * @var string
     */
    protected ?string $deleteEventClass = UserDeleted::class;

    /**
     * Правила валидации для пользователя
     *
     * @var array
     */
    protected array $validationRules = [
        'username' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ];

    /**
     * Правила валидации для обновления пользователя
     *
     * @var array
     */
    protected array $updateValidationRules = [
        'username' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|max:255|unique:users',
        'password' => 'sometimes|string|min:8',
    ];

    /**
     * Пользовательские сообщения валидации
     *
     * @var array
     */
    protected $validationMessages = [
        'email.unique' => 'Пользователь с таким email уже существует',
        'password.min' => 'Пароль должен содержать не менее :min символов',
    ];

    /**
     * Конструктор
     *
     * @param UserRepository|null $repository
     */
    public function __construct(?UserRepository $repository = null)
    {
        parent::__construct($repository);
        $this->setLogPrefix('UserService');
    }

    /**
     * Аутентификация пользователя
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return User|null
     */
    public function login(string $email, string $password, bool $remember = false): ?User
    {
        $this->withContext('action', 'login')
             ->withContext('email', $email);

        try {
            $credentials = [
                'email' => $email,
                'password' => $password,
            ];

            if (Auth::attempt($credentials, $remember)) {
                $user = Auth::user();

                $this->logInfo('Успешная авторизация пользователя', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);

                return $user;
            }

            $this->logWarning('Неудачная попытка авторизации', [
                'email' => $email,
            ]);

            return null;
        } catch (\Exception $e) {
            $this->logError('Ошибка при авторизации', [
                'email' => $email,
            ], $e);

            throw $e;
        }
    }

    /**
     * Выход пользователя из системы
     *
     * @return bool
     */
    public function logout(): bool
    {
        $userId = Auth::id();

        $this->withContext('action', 'logout')
             ->withContext('user_id', $userId);

        try {
            Auth::logout();

            $this->logInfo('Пользователь вышел из системы', [
                'user_id' => $userId,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError('Ошибка при выходе из системы', [
                'user_id' => $userId,
            ], $e);

            return false;
        }
    }

    /**
     * Регистрация нового пользователя
     *
     * @param array $data
     * @param bool $autoLogin
     * @return User|null
     */
    public function register(array $data, bool $autoLogin = true): ?User
    {
        $this->withContext('action', 'register')
             ->withContext('auto_login', $autoLogin);

        // Хешируем пароль перед созданием
        $this->addSavePipe(function ($data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            return $data;
        });

        $user = $this->create($data);

        // Автоматически входим после регистрации, если нужно
        if ($autoLogin && $user) {
            Auth::login($user);
        }

        return $user;
    }

    /**
     * Обновление профиля пользователя
     *
     * @param int $userId
     * @param array $data
     * @return Model
     */
    public function updateProfile(int $userId, array $data): Model
    {
        $this->withContext('action', 'update_profile')
             ->withContext('user_id', $userId);

        // Удаляем пароль из данных обновления профиля
        $this->addSavePipe(function ($data) {
            unset($data['password'], $data['password_confirmation']);
            return $data;
        });

        return $this->update($userId, $data);
    }

    /**
     * Смена пароля пользователя
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $this->withContext('action', 'change_password')
             ->withContext('user_id', $userId);

        $user = $this->findById($userId);

        if (!$user) {
            $this->logWarning('Пользователь не найден при смене пароля', [
                'user_id' => $userId,
            ]);
            return false;
        }

        // Проверяем текущий пароль
        if (!Hash::check($currentPassword, $user->password)) {
            $this->logWarning('Неверный текущий пароль при смене пароля', [
                'user_id' => $userId,
            ]);
            return false;
        }

        try {
            // Обновляем пароль
            $user->password = Hash::make($newPassword);
            $user->save();

            // Сбрасываем кеш
            $this->clearCacheForModel($userId);

            $this->logInfo('Пароль пользователя успешно изменен', [
                'user_id' => $userId,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError('Ошибка при смене пароля', [
                'user_id' => $userId,
            ], $e);

            return false;
        }
    }

    /**
     * Запрос на сброс пароля
     *
     * @param string $email
     * @return bool
     */
    public function requestPasswordReset(string $email): bool
    {
        $this->withContext('action', 'request_password_reset')
             ->withContext('email', $email);

        try {
            $status = Password::sendResetLink(['email' => $email]);

            $isSuccess = $status === Password::RESET_LINK_SENT;

            if ($isSuccess) {
                $this->logInfo('Запрос на сброс пароля отправлен', [
                    'email' => $email,
                ]);
            } else {
                $this->logWarning('Не удалось отправить запрос на сброс пароля', [
                    'email' => $email,
                    'status' => $status,
                ]);
            }

            return $isSuccess;
        } catch (\Exception $e) {
            $this->logError('Ошибка при запросе сброса пароля', [
                'email' => $email,
            ], $e);

            return false;
        }
    }

    /**
     * Сброс пароля
     *
     * @param array $credentials
     * @return bool
     */
    public function resetPassword(array $credentials): bool
    {
        $this->withContext('action', 'reset_password')
             ->withContext('email', $credentials['email'] ?? null);

        try {
            $status = Password::reset($credentials, function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();

                // Сбрасываем кеш
                $this->clearCacheForModel($user->id);
            });

            $isSuccess = $status === Password::PASSWORD_RESET;

            if ($isSuccess) {
                $this->logInfo('Пароль успешно сброшен', [
                    'email' => $credentials['email'] ?? null,
                ]);
            } else {
                $this->logWarning('Не удалось сбросить пароль', [
                    'email' => $credentials['email'] ?? null,
                    'status' => $status,
                ]);
            }

            return $isSuccess;
        } catch (\Exception $e) {
            $this->logError('Ошибка при сбросе пароля', [
                'email' => $credentials['email'] ?? null,
            ], $e);

            return false;
        }
    }

    /**
     * Получить текущего авторизованного пользователя
     *
     * @return Authenticatable|null
     */
    public function getCurrentUser(): ?Authenticatable
    {
        return Auth::user();
    }

    /**
     * Получить ID текущего авторизованного пользователя
     *
     * @return int|null
     */
    public function getCurrentUserId(): ?int
    {
        return Auth::id();
    }

    /**
     * Проверить, является ли пользователь текущим авторизованным
     *
     * @param int $userId
     * @return bool
     */
    public function isCurrentUser(int $userId): bool
    {
        return Auth::id() === $userId;
    }

    /**
     * Получить подписчиков пользователя
     *
     * @param int $userId
     * @return mixed
     */
    public function getFollowers(int $userId)
    {
        $cacheKey = $this->buildCacheKey('followers', $userId);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            /** @var UserRepository $repository */
            $repository = $this->getRepository();

            try {
                $result = $repository->getFollowers($userId);

                $this->logInfo('Получены подписчики пользователя', [
                    'user_id' => $userId,
                    'count' => count($result),
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logError('Ошибка при получении подписчиков', [
                    'user_id' => $userId,
                ], $e);

                throw $e;
            }
        });
    }

    /**
     * Получить подписки пользователя
     *
     * @param int $userId
     * @return mixed
     */
    public function getFollowing(int $userId)
    {
        $cacheKey = $this->buildCacheKey('following', $userId);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            /** @var UserRepository $repository */
            $repository = $this->getRepository();

            try {
                $result = $repository->getFollowing($userId);

                $this->logInfo('Получены подписки пользователя', [
                    'user_id' => $userId,
                    'count' => count($result),
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logError('Ошибка при получении подписок', [
                    'user_id' => $userId,
                ], $e);

                throw $e;
            }
        });
    }

    /**
     * Проверить, подписан ли один пользователь на другого
     *
     * @param int $userId
     * @param int $followingId
     * @return bool
     */
    public function isFollowing(int $userId, int $followingId): bool
    {
        $cacheKey = $this->buildCacheKey('is_following', $userId, $followingId);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId, $followingId) {
            /** @var UserRepository $repository */
            $repository = $this->getRepository();

            try {
                $result = $repository->isFollowing($userId, $followingId);

                $this->logInfo('Проверка подписки пользователя', [
                    'user_id' => $userId,
                    'following_id' => $followingId,
                    'is_following' => $result,
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logError('Ошибка при проверке подписки', [
                    'user_id' => $userId,
                    'following_id' => $followingId,
                ], $e);

                return false;
            }
        });
    }

    /**
     * Подписаться на пользователя
     *
     * @param int $userId
     * @param int $followingId
     * @return bool
     */
    public function followUser(int $userId, int $followingId): bool
    {
        $this->withContext('action', 'follow_user')
             ->withContext('user_id', $userId)
             ->withContext('following_id', $followingId);

        // Проверяем, не пытается ли пользователь подписаться сам на себя
        if ($userId === $followingId) {
            $this->logWarning('Попытка подписаться на самого себя', [
                'user_id' => $userId,
            ]);
            return false;
        }

        // Проверяем, существуют ли оба пользователя
        if (!$this->findById($userId) || !$this->findById($followingId)) {
            $this->logWarning('Один из пользователей не существует при подписке', [
                'user_id' => $userId,
                'following_id' => $followingId,
            ]);
            return false;
        }

        try {
            /** @var UserRepository $repository */
            $repository = $this->getRepository();

            $result = $repository->follow($userId, $followingId);

            if ($result) {
                // Очищаем кеш подписок
                $this->clearFollowCache($userId, $followingId);

                $this->logInfo('Пользователь успешно подписался', [
                    'user_id' => $userId,
                    'following_id' => $followingId,
                ]);
            } else {
                $this->logWarning('Не удалось подписаться на пользователя', [
                    'user_id' => $userId,
                    'following_id' => $followingId,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logError('Ошибка при подписке на пользователя', [
                'user_id' => $userId,
                'following_id' => $followingId,
            ], $e);

            return false;
        }
    }

    /**
     * Отписаться от пользователя
     *
     * @param int $userId
     * @param int $followingId
     * @return bool
     */
    public function unfollowUser(int $userId, int $followingId): bool
    {
        $this->withContext('action', 'unfollow_user')
             ->withContext('user_id', $userId)
             ->withContext('following_id', $followingId);

        // Проверяем, существуют ли оба пользователя
        if (!$this->findById($userId) || !$this->findById($followingId)) {
            $this->logWarning('Один из пользователей не существует при отписке', [
                'user_id' => $userId,
                'following_id' => $followingId,
            ]);
            return false;
        }

        try {
            /** @var UserRepository $repository */
            $repository = $this->getRepository();

            $result = $repository->unfollow($userId, $followingId);

            if ($result) {
                // Очищаем кеш подписок
                $this->clearFollowCache($userId, $followingId);

                $this->logInfo('Пользователь успешно отписался', [
                    'user_id' => $userId,
                    'following_id' => $followingId,
                ]);
            } else {
                $this->logWarning('Не удалось отписаться от пользователя', [
                    'user_id' => $userId,
                    'following_id' => $followingId,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logError('Ошибка при отписке от пользователя', [
                'user_id' => $userId,
                'following_id' => $followingId,
            ], $e);

            return false;
        }
    }

    /**
     * Получить количество подписчиков пользователя
     *
     * @param int $userId
     * @return int
     */
    public function getFollowersCount(int $userId): int
    {
        $cacheKey = $this->buildCacheKey('followers_count', $userId);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            /** @var UserRepository $repository */
            $repository = $this->getRepository();

            try {
                $result = $repository->getFollowersCount($userId);

                $this->logInfo('Получено количество подписчиков пользователя', [
                    'user_id' => $userId,
                    'count' => $result,
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logError('Ошибка при получении количества подписчиков', [
                    'user_id' => $userId,
                ], $e);

                return 0;
            }
        });
    }

    /**
     * Получить количество подписок пользователя
     *
     * @param int $userId
     * @return int
     */
    public function getFollowingCount(int $userId): int
    {
        $cacheKey = $this->buildCacheKey('following_count', $userId);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            /** @var UserRepository $repository */
            $repository = $this->getRepository();

            try {
                $result = $repository->getFollowingCount($userId);

                $this->logInfo('Получено количество подписок пользователя', [
                    'user_id' => $userId,
                    'count' => $result,
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logError('Ошибка при получении количества подписок', [
                    'user_id' => $userId,
                ], $e);

                return 0;
            }
        });
    }

    /**
     * Очистить кеш подписок
     *
     * @param int $userId
     * @param int $followingId
     * @return void
     */
    protected function clearFollowCache(int $userId, int $followingId): void
    {
        $this->forgetCache([
            $this->buildCacheKey('followers', $followingId),
            $this->buildCacheKey('following', $userId),
            $this->buildCacheKey('followers_count', $followingId),
            $this->buildCacheKey('following_count', $userId),
            $this->buildCacheKey('is_following', $userId, $followingId),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function canUpdate($user): bool
    {
        // Может обновлять только сам пользователь или администратор
        return $this->isCurrentUser($user->id) || $this->isAdmin();
    }

    /**
     * {@inheritdoc}
     */
    protected function canDelete($user): bool
    {
        // Может удалять только администратор
        return $this->isAdmin();
    }

    /**
     * Проверить, является ли текущий пользователь администратором
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->is_admin;
    }

    /**
     * {@inheritdoc}
     */
    protected function clearServiceCache(int $id): void
    {
        parent::clearServiceCache($id);

        // Очищаем кеш для текущего пользователя
        if ($this->isCurrentUser($id)) {
            $this->forgetCache('current_user');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function afterCreate(Model $model, array $data): void
    {
        $this->logInfo('Создан новый пользователь', [
            'user_id' => $model->id,
            'email' => $model->email,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function afterUpdate(Model $model, array $data): void
    {
        $this->logInfo('Обновлен профиль пользователя', [
            'user_id' => $model->id,
            'updated_fields' => array_keys($data),
        ]);
    }

    /**
     * Найти пользователя по ID
     *
     * @param int $id
     * @return User|null
     */
    public function findUserById(int $id)
    {
        $this->logInfo("Поиск пользователя по ID: {$id}");
        
        $cacheKey = $this->buildCacheKey('user', $id);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            return $this->findById($id);
        });
    }

    /**
     * Создать нового пользователя
     *
     * @param array $data Данные пользователя
     * @return User|null
     */
    public function createUser(array $data): ?User
    {
        $this->logInfo("Создание нового пользователя", ['email' => $data['email'] ?? null]);
        
        try {
            // Хешируем пароль, если он предоставлен
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            
            // Создаем уникальный slug, если не предоставлен
            if (!isset($data['slug']) && isset($data['username'])) {
                $data['slug'] = Str::slug($data['username']) . '-' . uniqid();
            }
            
            $user = $this->create($data);
            
            if ($user) {
                $this->logInfo("Пользователь успешно создан", ['id' => $user->id]);
            } else {
                $this->logWarning("Не удалось создать пользователя");
            }
            
            return $user;
        } catch (Exception $e) {
            $this->logError("Ошибка при создании пользователя", [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage()
            ], $e);
            
            throw $e;
        }
    }
}
