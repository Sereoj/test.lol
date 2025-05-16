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
            // Получаем Personal Access Client (он создаётся через php artisan passport:install)
            $client = Client::query()->where('personal_access_client', 1)->first();
            Log::info('Personal Access Client: ', ['client' => $client]);

            if (! $client) {
                throw new Exception('OAuth personal access client not found');
            }

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

            Log::info('Access Token Expires At: '.$token->token->expires_at);
            Log::info('Refresh Token Expires At: '.$refreshTokenExpiresAt);

            return [
                'token_type' => 'Bearer',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => Carbon::parse($token->token->expires_at)->toIso8601String(),
            ];
        } catch (Exception $e) {
            Log::error('Token generation error: ', [
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
}
