<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);

        return redirect()->intended('dashboard');
    }

    protected function findOrCreateUser($user, $provider)
    {
        $authUser = User::query()->where('provider_id', $user->id)->first();

        if ($authUser) {
            return $authUser;
        }

        /*        return User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'provider' => $provider,
                    'provider_id' => $user->id,
                    //'password' => Hash::make(str_random(24)),
                ]);*/
    }
}
