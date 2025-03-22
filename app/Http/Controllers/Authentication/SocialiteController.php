<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_SOCIAL_USER = 'social_user_';

    public function redirectToProvider($provider)
    {
        try {
            Log::info('Redirecting to social provider', ['provider' => $provider, 'user_id' => Auth::id()]);
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            Log::error('Error redirecting to social provider: ' . $e->getMessage(), [
                'provider' => $provider,
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse('Unable to connect to ' . $provider . '. Please try again.', 500);
        }
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $authUser = $this->findOrCreateUser($socialUser, $provider);
            
            if (!$authUser) {
                Log::warning('Failed to find or create user from social provider', [
                    'provider' => $provider, 
                    'social_id' => $socialUser->id
                ]);
                return $this->errorResponse('Unable to login with ' . $provider . '. Please try again.', 400);
            }
            
            Auth::login($authUser, true);
            
            Log::info('User logged in via social provider', [
                'provider' => $provider, 
                'user_id' => $authUser->id
            ]);
            
            return redirect()->intended('dashboard');
        } catch (Exception $e) {
            Log::error('Error handling social provider callback: ' . $e->getMessage(), [
                'provider' => $provider
            ]);
            return $this->errorResponse('Unable to login with ' . $provider . '. Please try again.', 500);
        }
    }

    protected function findOrCreateUser($user, $provider)
    {
        try {
            $cacheKey = self::CACHE_KEY_SOCIAL_USER . $provider . '_' . $user->id;
            
            $authUser = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
                return User::query()->where('provider_id', $user->id)->first();
            });

            if ($authUser) {
                return $authUser;
            }

            // Закомментированный код создания пользователя заменен на null
            // Если нужно имплементировать создание пользователя, раскомментируйте код ниже
            /*return User::create([
                'name' => $user->name,
                'email' => $user->email,
                'provider' => $provider,
                'provider_id' => $user->id,
                //'password' => Hash::make(str_random(24)),
            ]);*/
            
            return null;
        } catch (Exception $e) {
            Log::error('Error finding or creating user from social provider: ' . $e->getMessage(), [
                'provider' => $provider, 
                'social_id' => $user->id
            ]);
            return null;
        }
    }
}
