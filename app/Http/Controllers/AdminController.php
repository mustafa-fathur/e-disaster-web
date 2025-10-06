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

        return view('admin.users', compact('users'));
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

        return view('admin.volunteers', compact('volunteers'));
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
    public function rejectVolunteer(User $user, Request $request)
    {
        if ($user->type !== UserTypeEnum::VOLUNTEER || $user->status !== UserStatusEnum::REGISTERED) {
            return redirect()->back()->withErrors(['error' => 'Invalid volunteer status.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user->update([
            'status' => UserStatusEnum::INACTIVE,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()->with('success', 'Volunteer rejected with reason saved.');
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
     * Officers list page.
     */
    public function officers(Request $request): View
    {
        $officers = User::where('type', UserTypeEnum::OFFICER)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($s) use ($request) {
                    $s->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.officers', compact('officers'));
    }

    // Removed editOfficer view endpoint; edit handled via modal on index

    /** Update officer */
    public function updateOfficer(User $user, Request $request)
    {
        abort_unless($user->type === UserTypeEnum::OFFICER, 404);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $update = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ];
        if (!empty($validated['password'])) {
            $update['password'] = bcrypt($validated['password']);
        }
        $user->update($update);

        return redirect()->route('admin.officers')->with('success', 'Officer updated successfully.');
    }

    /** Delete officer */
    public function destroyOfficer(User $user)
    {
        abort_unless($user->type === UserTypeEnum::OFFICER, 404);
        $user->delete();
        return redirect()->route('admin.officers')->with('success', 'Officer deleted successfully.');
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