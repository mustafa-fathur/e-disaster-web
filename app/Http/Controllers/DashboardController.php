<?php

namespace App\Http\Controllers;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user === null || $user->status !== UserStatusEnum::ACTIVE) {
            abort(403);
        }

        if ($user->type === UserTypeEnum::ADMIN) {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('status', UserStatusEnum::ACTIVE)->count(),
                'admin_users' => User::where('type', UserTypeEnum::ADMIN)->count(),
                'officer_users' => User::where('type', UserTypeEnum::OFFICER)->count(),
                'volunteer_users' => User::where('type', UserTypeEnum::VOLUNTEER)->count(),
                'registered_volunteers' => User::where('type', UserTypeEnum::VOLUNTEER)
                    ->where('status', UserStatusEnum::REGISTERED)
                    ->count(),
            ];

            return view('admin.dashboard', compact('stats'));
        }

        return view('dashboard');
    }
}
