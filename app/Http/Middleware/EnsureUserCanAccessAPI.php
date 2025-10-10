<?php

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('sanctum')->check()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = auth('sanctum')->user();

        // Check if user is active
        if ($user->status !== UserStatusEnum::ACTIVE) {
            return response()->json([
                'message' => 'Your account is not active. Please contact an administrator.'
            ], 403);
        }

        // Only officers and volunteers can access API (admin is web-only)
        if (!in_array($user->type, [UserTypeEnum::OFFICER, UserTypeEnum::VOLUNTEER])) {
            return response()->json([
                'message' => 'Access denied. API access is restricted to officers and volunteers only.'
            ], 403);
        }

        return $next($request);
    }
}