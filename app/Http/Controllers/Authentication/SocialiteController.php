<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Exception;

// Контроллер для работы с социальными сетями
class SocialiteController extends Controller
{
    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_SOCIAL_USER = 'social_user_';

    public function redirectToProvider($provider)
    {
        try {
            Log::info('Перенаправление к социальному провайдеру', ['provider' => $provider, 'user_id' => Auth::id()]);
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            Log::error('Ошибка перенаправления к социальному провайдеру: ' . $e->getMessage(), [
                'provider' => $provider,
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse('Unable to connect to ' . $provider . '. Please try again.', 500);
        }
    }

    // Обработка обратного вызова от социальной сети
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $authUser = $this->findOrCreateUser($socialUser, $provider);

            if (!$authUser) {
                Log::warning('Не удалось найти или создать пользователя через социальный провайдер', [
                    'provider' => $provider,
                    'social_id' => $socialUser->id
                ]);
                return $this->errorResponse('Unable to login with ' . $provider . '. Please try again.', 400);
            }

            Auth::login($authUser, true);

            Log::info('Пользователь вошел через социальный провайдер', [
                'provider' => $provider,
                'user_id' => $authUser->id
            ]);

            return redirect()->intended('dashboard');
        } catch (Exception $e) {
            Log::error('Ошибка обработки обратного вызова социального провайдера: ' . $e->getMessage(), [
                'provider' => $provider
            ]);
            return $this->errorResponse('Unable to login with ' . $provider . '. Please try again.', 500);
        }
    }

    // Поиск или создание пользователя из социальной сети
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
            Log::error('Ошибка поиска или создания пользователя через социальный провайдер: ' . $e->getMessage(), [
                'provider' => $provider,
                'social_id' => $user->id
            ]);
            return null;
        }
    }
}
