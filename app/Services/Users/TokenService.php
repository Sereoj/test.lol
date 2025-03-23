<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Services\Base\AppSettingsService;
use App\Services\Base\SimpleService;
use Carbon\Carbon;
use Exception;
use Laravel\Passport\Client;
use Laravel\Passport\Token;

/**
 * Сервис для работы с токенами авторизации
 */
class TokenService extends SimpleService
{
    /**
     * Сервис настроек приложения
     *
     * @var AppSettingsService
     */
    protected AppSettingsService $appSettingsService;

    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'token';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 30;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->appSettingsService = new AppSettingsService();
        $this->setLogPrefix('TokenService');
    }

    /**
     * Генерация токенов доступа и обновления
     *
     * @param User $user
     * @return array
     * @throws Exception|\Throwable
     */
    public function generateTokens($user): array
    {
        $this->logInfo('Генерация токенов для пользователя', ['user_id' => $user->id]);

        return $this->transaction(function () use ($user) {
            try {
                // Получаем клиент с флагом `password_client`
                $client = Client::query()->where('password_client', 1)->first();
                if (!$client) {
                    $this->logError('OAuth клиент с типом password не найден');
                    throw new Exception('OAuth client with password not found');
                }

                // Генерируем access_token
                $scope = 'access_api';
                $this->logInfo('Запрошенная область действия: ' . $scope);
                $accessTokenResult = $user->createToken('access_token', [$scope]);
                $accessToken = $accessTokenResult->accessToken;

                $refreshToken = $this->generateRefreshToken();
                $refreshTokenExpiresAt = Carbon::now()->addMonths(
                    $this->appSettingsService->get('tokens.refresh_token', 1)
                );

                // Сохраняем refresh_token в базе данных (вместо стандартной логики)
                Token::create([
                    'id' => $refreshToken,
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'revoked' => false,
                    'created_at' => Carbon::now(),
                    'expires_at' => $refreshTokenExpiresAt,
                ]);

                // Логируем время истечения токенов
                $this->logInfo('Время истечения токенов', [
                    'access_token_expires_at' => $accessTokenResult->token->expires_at,
                    'refresh_token_expires_at' => $refreshTokenExpiresAt
                ]);

                return [
                    'token_type' => 'Bearer',
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_at' => Carbon::parse($accessTokenResult->token->expires_at)->toIso8601String(),
                ];
            } catch (Exception $e) {
                $this->logError('Ошибка генерации токенов', ['user_id' => $user->id], $e);
                throw $e;
            }
        });
    }

    /**
     * Обновление access_token с использованием refresh_token
     *
     * @param string $refreshToken
     * @return array
     * @throws Exception
     */
    public function refreshToken(string $refreshToken): array
    {
        $this->logInfo('Запрос на обновление токена', ['refresh_token' => $refreshToken]);

        return $this->transaction(function () use ($refreshToken) {
            try {
                // Проверяем refresh_token в базе данных
                $token = Token::query()->where('id', $refreshToken)->first();

                if (!$token || $token->revoked) {
                    $this->logWarning('Недействительный или отозванный refresh_token', ['refresh_token' => $refreshToken]);
                    throw new Exception('Invalid or revoked refresh token');
                }

                // Проверяем срок действия refresh_token
                if (Carbon::parse($token->expires_at)->isPast()) {
                    $token->delete();
                    $this->logWarning('Срок действия refresh_token истек', ['refresh_token' => $refreshToken]);
                    throw new Exception('Refresh token has expired');
                }

                // Получаем пользователя из токена
                $user = $token->user;

                if (!$user) {
                    $this->logWarning('Недействительный refresh_token - пользователь не найден', ['refresh_token' => $refreshToken]);
                    throw new Exception('Invalid refresh token');
                }

                // Удаляем старый refresh_token
                $token->delete();

                // Генерируем новые токены
                $this->logInfo('Генерация новых токенов при обновлении', ['user_id' => $user->id]);
                return $this->generateTokens($user);
            } catch (Exception $e) {
                $this->logError('Ошибка при обновлении токена', ['refresh_token' => $refreshToken], $e);
                throw $e;
            }
        });
    }

    /**
     * Очистка просроченных токенов
     */
    public function cleanupExpiredTokens(): void
    {
        $this->logInfo('Очистка просроченных токенов');

        try {
            $now = Carbon::now();
            $count = Token::query()
                ->where('expires_at', '<', $now)
                ->delete();

            $this->logInfo('Удалены просроченные токены', ['count' => $count]);
        } catch (Exception $e) {
            $this->logError('Ошибка при очистке просроченных токенов', [], $e);
        }
    }

    /**
     * Генерация строки refresh_token
     *
     * @return string
     */
    private function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(40));
    }
}
