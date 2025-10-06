<?php

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user is active
        if ($user->status !== UserStatusEnum::ACTIVE) {
            auth()->logout();
            
            return redirect()->route('login')->withErrors([
                'status' => 'Your account is not active. Please contact an administrator.'
            ]);
        }

        // All user types can access web (admin, officer, volunteer)
        // This middleware is for future extensibility
        if (!in_array($user->type, [UserTypeEnum::ADMIN, UserTypeEnum::OFFICER, UserTypeEnum::VOLUNTEER])) {
            abort(403, 'Access denied. Invalid user type.');
        }

        return $next($request);
    }
}