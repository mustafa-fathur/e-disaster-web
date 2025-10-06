<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserTypeEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', UserStatusEnum::ACTIVE)->count(),
            'admin_users' => User::where('type', UserTypeEnum::ADMIN)->count(),
            'officer_users' => User::where('type', UserTypeEnum::OFFICER)->count(),
            'volunteer_users' => User::where('type', UserTypeEnum::VOLUNTEER)->count(),
            'registered_volunteers' => User::where('type', UserTypeEnum::VOLUNTEER)
                ->where('status', UserStatusEnum::REGISTERED)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Display all users for management.
     */
    public function users(Request $request): View
    {
        $query = User::query();

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display volunteers awaiting approval.
     */
    public function volunteers(): View
    {
        $volunteers = User::where('type', UserTypeEnum::VOLUNTEER)
            ->where('status', UserStatusEnum::REGISTERED)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.volunteers.index', compact('volunteers'));
    }

    /**
     * Approve a volunteer.
     */
    public function approveVolunteer(User $user)
    {
        if ($user->type !== UserTypeEnum::VOLUNTEER || $user->status !== UserStatusEnum::REGISTERED) {
            return redirect()->back()->withErrors(['error' => 'Invalid volunteer status.']);
        }

        $user->update(['status' => UserStatusEnum::ACTIVE]);

        return redirect()->back()->with('success', 'Volunteer approved successfully.');
    }

    /**
     * Reject a volunteer.
     */
    public function rejectVolunteer(User $user)
    {
        if ($user->type !== UserTypeEnum::VOLUNTEER || $user->status !== UserStatusEnum::REGISTERED) {
            return redirect()->back()->withErrors(['error' => 'Invalid volunteer status.']);
        }

        $user->update(['status' => UserStatusEnum::INACTIVE]);

        return redirect()->back()->with('success', 'Volunteer rejected.');
    }

    /**
     * Create a new officer.
     */
    public function createOfficer(): View
    {
        return view('admin.officers.create');
    }

    /**
     * Store a new officer.
     */
    public function storeOfficer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'type' => UserTypeEnum::OFFICER,
            'status' => UserStatusEnum::ACTIVE,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users')->with('success', 'Officer created successfully.');
    }

    /**
     * Display user details.
     */
    public function showUser(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Update user status.
     */
    public function updateUserStatus(User $user, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $user->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'User status updated successfully.');
    }
}