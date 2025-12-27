<?php

namespace App\Services\Users;

use App\Services\Base\AppSettingsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Laravel\Passport\Token;

class TokenService
{
    protected AppSettingsService $appSettingsService;

    public function __construct()
    {
        $this->appSettingsService = new AppSettingsService();
    }

    /**
     * Generate access_token and custom refresh_token.
     *
     * @throws Exception
     */
    public function generateTokens($user): array
    {
        try {
            // Получаем или создаём Personal Access Client
            $client = $this->ensurePersonalAccessClientExists();

            Log::info('Using Personal Access Client', [
                'client_id' => $client->id,
                'client_name' => $client->name,
            ]);

            // Генерируем access_token
            $scope = 'access_api';
            $token = $user->createToken('access_token', [$scope]);
            $accessToken = $token->accessToken;

            // Генерируем refresh_token
            $refreshToken = $this->generateRefreshToken();
            $refreshTokenExpiresAt = Carbon::now()->addMonths($this->appSettingsService->get('tokens.refresh_token'));

            // Сохраняем refresh_token вручную
            Token::create([
                'id' => $refreshToken,
                'user_id' => $user->id,
                'client_id' => $client->id,
                'revoked' => false,
                'created_at' => Carbon::now(),
                'expires_at' => $refreshTokenExpiresAt,
            ]);

            Log::info('Tokens generated successfully', [
                'user_id' => $user->id,
                'access_token_expires_at' => $token->token->expires_at,
                'refresh_token_expires_at' => $refreshTokenExpiresAt,
            ]);

            return [
                'token_type' => 'Bearer',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => Carbon::parse($token->token->expires_at)->toIso8601String(),
            ];
        } catch (Exception $e) {
            Log::error('Token generation error: ', [
                'user_id' => $user->id ?? null,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }

    /**
     * Refresh access_token using refresh_token.
     *
     * @throws Exception
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            // Проверяем refresh_token
            $token = Token::query()->where('id', $refreshToken)->first();

            if (! $token || $token->revoked || Carbon::parse($token->expires_at)->isPast()) {
                throw new Exception('Invalid or expired refresh token');
            }

            $user = $token->user;

            if (! $user) {
                throw new Exception('User not found for refresh token');
            }

            // Удаляем старый refresh_token
            $token->delete();

            // Генерируем новые токены
            return $this->generateTokens($user);
        } catch (Exception $e) {
            Log::error('Token refresh error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Cleanup expired tokens.
     */
    public function cleanupExpiredTokens(): void
    {
        try {
            $now = Carbon::now();
            $deletedTokens = Token::query()->where('expires_at', '<', $now)->delete();
            Log::info('Expired tokens deleted: '.$deletedTokens);
        } catch (Exception $e) {
            Log::error('Token cleanup error: '.$e->getMessage());
        }
    }

    /**
     * Generate custom refresh_token.
     */
    private function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(40)); // 80 hex chars
    }

    /**
     * Ensure Personal Access Client exists, create if not found.
     *
     * @throws Exception
     */
    private function ensurePersonalAccessClientExists(): Client
    {
        // Ищем существующий Personal Access Client по типу
        $client = Client::query()->where('personal_access_client', 1)->first();

        if ($client) {
            Log::info('Personal Access Client found in database', [
                'client_id' => $client->id,
            ]);
            return $client;
        }

        // Клиент не найден - создаём автоматически
        Log::warning('Personal Access Client not found, creating automatically...');

        try {
            $clientRepository = new \Laravel\Passport\ClientRepository();
            $client = $clientRepository->createPersonalAccessClient(
                null,
                'Laravel Personal Access Client',
                config('app.url')
            );

            Log::info('Personal Access Client created successfully', [
                'client_id' => $client->id,
                'client_secret' => $client->secret,
            ]);

            // Опционально: синхронизируем с .env если файл доступен
            $this->syncClientToEnvFile($client);

            return $client;
        } catch (Exception $e) {
            Log::error('Failed to create Personal Access Client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Failed to create OAuth Personal Access Client: ' . $e->getMessage());
        }
    }

    /**
     * Sync client credentials to .env file (optional).
     */
    private function syncClientToEnvFile(Client $client): void
    {
        try {
            $envFile = base_path('.env');

            if (!file_exists($envFile) || !is_writable($envFile)) {
                Log::warning('.env file not writable, skipping sync');
                return;
            }

            $content = file_get_contents($envFile);

            // Обновляем или добавляем PASSPORT_PERSONAL_ACCESS_CLIENT_ID
            if (preg_match('/^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=/m', $content)) {
                $content = preg_replace(
                    '/^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=.*/m',
                    "PASSPORT_PERSONAL_ACCESS_CLIENT_ID={$client->id}",
                    $content
                );
            } else {
                $content .= "\nPASSPORT_PERSONAL_ACCESS_CLIENT_ID={$client->id}\n";
            }

            // Обновляем или добавляем PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET
            if (preg_match('/^PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=/m', $content)) {
                $content = preg_replace(
                    '/^PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=.*/m',
                    "PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET={$client->secret}",
                    $content
                );
            } else {
                $content .= "PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET={$client->secret}\n";
            }

            file_put_contents($envFile, $content);

            Log::info('.env file synchronized with Personal Access Client credentials');
        } catch (Exception $e) {
            Log::warning('Failed to sync .env file', ['error' => $e->getMessage()]);
            // Не бросаем исключение - это не критичная операция
        }
    }
}
