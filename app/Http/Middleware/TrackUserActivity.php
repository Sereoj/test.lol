<?php

namespace App\Http\Middleware;

use App\Events\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrackUserActivity
{
    protected $except = [
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = "user_activity_{$user->id}";

            try {
                if (! Cache::has($cacheKey)) {
                    event(new UserActivity(
                        $user,
                        $request->header('User-Agent'),
                        $request->ip()
                    ));
                    Cache::put($cacheKey, true, now()->addMinutes(2));
                    Log::info("User {$user->id} is active. Route: ".($request->route() ? $request->route()->getName() : 'No route name'));
                }
            } catch (\Exception $e) {
                Log::error('Failed to track user activity: '.$e->getMessage());
            }
        }

        return $next($request);
    }

    public function shouldIgnore(Request $request)
    {
        foreach ($this->except as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }
}
