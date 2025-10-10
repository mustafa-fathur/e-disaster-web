<?php

namespace App\Http\Middleware;

use App\Models\DisasterVolunteer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAssignedToDisaster
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
        $disasterId = $request->route('id');

        if (!$disasterId) {
            return response()->json([
                'message' => 'Disaster ID is required.'
            ], 400);
        }

        // Check if user is assigned to this disaster
        $isAssigned = DisasterVolunteer::where('disaster_id', $disasterId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'message' => 'Access denied. You are not assigned to this disaster.'
            ], 403);
        }

        return $next($request);
    }
}
