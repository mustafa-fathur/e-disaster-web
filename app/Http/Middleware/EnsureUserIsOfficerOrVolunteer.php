<?php

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOfficerOrVolunteer
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

        // Check if user is officer or volunteer
        if (!in_array($user->type, [UserTypeEnum::OFFICER, UserTypeEnum::VOLUNTEER])) {
            abort(403, 'Access denied. Officer or Volunteer privileges required.');
        }

        return $next($request);
    }
}