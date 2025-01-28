<?php

namespace App\Services;

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
        $this->appSettingsService = new AppSettingsService;
    }

    /**
     * Generate access_token and custom refresh_token.
     *
     * @throws Exception
     */
    public function generateTokens($user): array
    {
        try {
            // Get the client with the `password_client` flag
            $client = Client::query()->where('password_client', 1)->first();
            if (! $client) {
                throw new Exception('OAuth client with password not found');
            }

            // Generate access_token
            $scope = 'access_api';
            Log::info('Requested Scope: '.$scope);
            $accessTokenResult = $user->createToken('access_token', [$scope]);
            $accessToken = $accessTokenResult->accessToken;

            $refreshToken = $this->generateRefreshToken();
            $refreshTokenExpiresAt = Carbon::now()->addMonths($this->appSettingsService->get('tokens.refresh_token'));

            // Save refresh_token in the database (instead of standard logic)
            Token::create([
                'id' => $refreshToken,
                'user_id' => $user->id,
                'client_id' => $client->id,
                'revoked' => false,
                'created_at' => Carbon::now(),
                'expires_at' => $refreshTokenExpiresAt,
            ]);

            // Log token expiration times
            Log::info('Access Token Expires At: '.$accessTokenResult->token->expires_at);
            Log::info('Refresh Token Expires At: '.$refreshTokenExpiresAt);

            return [
                'token_type' => 'Bearer',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => Carbon::parse($accessTokenResult->token->expires_at)->toIso8601String(),
            ];
        } catch (Exception $e) {
            Log::error('Token generation error: '.$e->getMessage());
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
            // Check refresh_token in the database
            $token = Token::query()->where('id', $refreshToken)->first();

            if (! $token || $token->revoked) {
                throw new Exception('Invalid or revoked refresh token');
            }

            // Check refresh_token expiration
            if (Carbon::parse($token->expires_at)->isPast()) {
                $token->delete();
                throw new Exception('Refresh token has expired');
            }

            // Get the user from the token
            $user = $token->user;

            if (! $user) {
                throw new Exception('Invalid refresh token');
            }

            // Delete the old refresh_token
            $token->delete();

            // Generate new tokens
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
        return bin2hex(random_bytes(40)); // Example of generating a random string
    }
}
