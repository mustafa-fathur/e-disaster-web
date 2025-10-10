<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\DisasterReport;
use App\Models\DisasterVictim;
use App\Models\DisasterAid;
use App\Models\DisasterVolunteer;
use App\Models\Notification;
use App\Enums\DisasterTypeEnum;
use App\Enums\DisasterStatusEnum;
use App\Enums\DisasterSourceEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DisasterController extends Controller
{
    /**
     * Dashboard data for officers and volunteers
     */
    public function dashboard(Request $request)
    {
        $user = auth('sanctum')->user();
        
        // Get user's assigned disasters
        $assignedDisasters = DisasterVolunteer::where('user_id', $user->id)
            ->with('disaster')
            ->get()
            ->pluck('disaster')
            ->filter(); // Remove null disasters

        $assignedDisasterIds = $assignedDisasters->pluck('id')->toArray();

        // Dashboard statistics
        $stats = [
            'total_disasters' => Disaster::count(),
            'assigned_disasters' => $assignedDisasters->count(),
            'ongoing_disasters' => $assignedDisasters->where('status', 'ongoing')->count(),
            'completed_disasters' => $assignedDisasters->where('status', 'completed')->count(),
            'total_reports' => DisasterReport::whereIn('disaster_id', $assignedDisasterIds)->count(),
            'total_victims' => DisasterVictim::whereIn('disaster_id', $assignedDisasterIds)->count(),
            'total_aids' => DisasterAid::whereIn('disaster_id', $assignedDisasterIds)->count(),
        ];

        // Recent disasters (assigned to user)
        $recentDisasters = $assignedDisasters
            ->sortByDesc('created_at')
            ->take(5)
            ->map(function ($disaster) {
                return [
                    'id' => $disaster->id,
                    'title' => $disaster->title,
                    'type' => $disaster->types->value,
                    'status' => $disaster->status->value,
                    'location' => $disaster->location,
                    'date' => $disaster->date->format('Y-m-d'),
                    'time' => $disaster->time->format('H:i:s'),
                    'created_at' => $disaster->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        // Recent reports from assigned disasters
        $recentReports = DisasterReport::whereIn('disaster_id', $assignedDisasterIds)
            ->with('disaster')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'description' => $report->description,
                    'disaster_title' => $report->disaster->title,
                    'disaster_id' => $report->disaster_id,
                    'is_final_stage' => $report->is_final_stage,
                    'created_at' => $report->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Recent victims from assigned disasters
        $recentVictims = DisasterVictim::whereIn('disaster_id', $assignedDisasterIds)
            ->with('disaster')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($victim) {
                return [
                    'id' => $victim->id,
                    'name' => $victim->name,
                    'status' => $victim->status->value,
                    'is_evacuated' => $victim->is_evacuated,
                    'disaster_title' => $victim->disaster->title,
                    'disaster_id' => $victim->disaster_id,
                    'created_at' => $victim->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Recent aids from assigned disasters
        $recentAids = DisasterAid::whereIn('disaster_id', $assignedDisasterIds)
            ->with('disaster')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($aid) {
                return [
                    'id' => $aid->id,
                    'title' => $aid->title,
                    'category' => $aid->category->value,
                    'quantity' => $aid->quantity,
                    'unit' => $aid->unit,
                    'disaster_title' => $aid->disaster->title,
                    'disaster_id' => $aid->disaster_id,
                    'created_at' => $aid->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Unread notifications count
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user->type->value,
                'status' => $user->status->value,
            ],
            'stats' => $stats,
            'recent_disasters' => $recentDisasters,
            'recent_reports' => $recentReports,
            'recent_victims' => $recentVictims,
            'recent_aids' => $recentAids,
            'unread_notifications' => $unreadNotifications,
        ], 200);
    }

    /**
     * Get all disasters (with pagination)
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $type = $request->get('type');
        $search = $request->get('search');

        $query = Disaster::query();

        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('types', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $disasters = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $disasters->items(),
            'pagination' => [
                'current_page' => $disasters->currentPage(),
                'last_page' => $disasters->lastPage(),
                'per_page' => $disasters->perPage(),
                'total' => $disasters->total(),
                'from' => $disasters->firstItem(),
                'to' => $disasters->lastItem(),
            ]
        ], 200);
    }

    /**
     * Get specific disaster details
     */
    public function show(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $disaster->id,
                'title' => $disaster->title,
                'description' => $disaster->description,
                'source' => $disaster->source->value,
                'type' => $disaster->types->value,
                'status' => $disaster->status->value,
                'date' => $disaster->date->format('Y-m-d'),
                'time' => $disaster->time->format('H:i:s'),
                'location' => $disaster->location,
                'coordinate' => $disaster->coordinate,
                'lat' => $disaster->lat,
                'long' => $disaster->long,
                'magnitude' => $disaster->magnitude,
                'depth' => $disaster->depth,
                'reported_by' => $disaster->reported_by,
                'cancelled_reason' => $disaster->cancelled_reason,
                'cancelled_at' => $disaster->cancelled_at?->format('Y-m-d H:i:s'),
                'cancelled_by' => $disaster->cancelled_by,
                'completed_at' => $disaster->completed_at?->format('Y-m-d H:i:s'),
                'completed_by' => $disaster->completed_by,
                'created_at' => $disaster->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $disaster->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Create new disaster (Officer only)
     */
    public function createDisaster(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:45',
            'description' => 'nullable|string',
            'source' => 'required|in:bmkg,manual',
            'type' => 'required|in:earthquake,tsunami,volcanic_eruption,flood,drought,tornado,landslide,non_natural_disaster,social_disaster',
            'status' => 'nullable|in:cancelled,ongoing,completed',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i:s',
            'location' => 'nullable|string|max:45',
            'coordinate' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'magnitude' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('sanctum')->user();

        $disaster = Disaster::create([
            'title' => $request->title,
            'description' => $request->description,
            'source' => DisasterSourceEnum::from($request->source),
            'types' => DisasterTypeEnum::from($request->type),
            'status' => DisasterStatusEnum::from($request->status ?? 'ongoing'),
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'coordinate' => $request->coordinate,
            'lat' => $request->lat,
            'long' => $request->long,
            'magnitude' => $request->magnitude,
            'depth' => $request->depth,
            'reported_by' => $user->id,
        ]);

        // Automatically assign the creator as a volunteer
        DisasterVolunteer::create([
            'disaster_id' => $disaster->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Disaster created successfully. You have been automatically assigned as a volunteer.',
            'data' => [
                'id' => $disaster->id,
                'title' => $disaster->title,
                'type' => $disaster->types->value,
                'status' => $disaster->status->value,
                'location' => $disaster->location,
                'date' => $disaster->date->format('Y-m-d'),
                'time' => $disaster->time->format('H:i:s'),
                'created_at' => $disaster->created_at->format('Y-m-d H:i:s'),
                'auto_assigned' => true,
            ]
        ], 201);
    }

    /**
     * Update disaster (Officer only)
     */
    public function updateDisaster(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        // Check if disaster is cancelled or completed - cannot be modified
        if (in_array($disaster->status, [DisasterStatusEnum::CANCELLED, DisasterStatusEnum::COMPLETED])) {
            return response()->json([
                'message' => 'Cannot modify disaster. Disaster is already ' . $disaster->status->value . '.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:45',
            'description' => 'nullable|string',
            'source' => 'sometimes|required|in:bmkg,manual',
            'type' => 'sometimes|required|in:earthquake,tsunami,volcanic_eruption,flood,drought,tornado,landslide,non_natural_disaster,social_disaster',
            'status' => 'sometimes|required|in:cancelled,ongoing,completed',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required|date_format:H:i:s',
            'location' => 'nullable|string|max:45',
            'coordinate' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'magnitude' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'title', 'description', 'date', 'time', 'location', 
            'coordinate', 'lat', 'long', 'magnitude', 'depth'
        ]);

        // Handle enum fields
        if ($request->has('source')) {
            $updateData['source'] = DisasterSourceEnum::from($request->source);
        }
        if ($request->has('type')) {
            $updateData['types'] = DisasterTypeEnum::from($request->type);
        }
        if ($request->has('status')) {
            $updateData['status'] = DisasterStatusEnum::from($request->status);
        }

        $disaster->update($updateData);

        return response()->json([
            'message' => 'Disaster updated successfully.',
            'data' => [
                'id' => $disaster->id,
                'title' => $disaster->title,
                'type' => $disaster->types->value,
                'status' => $disaster->status->value,
                'location' => $disaster->location,
                'date' => $disaster->date->format('Y-m-d'),
                'time' => $disaster->time->format('H:i:s'),
                'updated_at' => $disaster->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Cancel disaster (Only for assigned volunteers/officers)
     */
    public function cancelDisaster(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        // Only ongoing disasters can be cancelled
        if ($disaster->status !== DisasterStatusEnum::ONGOING) {
            return response()->json([
                'message' => 'Only ongoing disasters can be cancelled. Current status: ' . $disaster->status->value
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'cancelled_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the disaster volunteer assignment for this user
        $user = auth('sanctum')->user();
        $disasterVolunteer = DisasterVolunteer::where('disaster_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$disasterVolunteer) {
            return response()->json([
                'message' => 'You are not assigned to this disaster.'
            ], 403);
        }

        $disaster->update([
            'status' => DisasterStatusEnum::CANCELLED,
            'cancelled_reason' => $request->cancelled_reason,
            'cancelled_at' => now(),
            'cancelled_by' => $disasterVolunteer->id
        ]);

        return response()->json([
            'message' => 'Disaster cancelled successfully.',
            'data' => [
                'id' => $disaster->id,
                'title' => $disaster->title,
                'status' => $disaster->status->value,
                'cancelled_reason' => $disaster->cancelled_reason,
                'cancelled_at' => $disaster->cancelled_at->format('Y-m-d H:i:s'),
                'cancelled_by' => $disasterVolunteer->id
            ]
        ], 200);
    }

    /**
     * Get volunteers assigned to a specific disaster
     */
    public function getDisasterVolunteers(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = DisasterVolunteer::where('disaster_id', $id)
            ->with('user');

        // Apply search filter
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $volunteers = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $volunteerData = $volunteers->items();
        $mappedVolunteers = collect($volunteerData)->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'disaster_id' => $assignment->disaster_id,
                'user_id' => $assignment->user_id,
                'user_name' => $assignment->user->name,
                'user_email' => $assignment->user->email,
                'user_type' => $assignment->user->type->value,
                'user_status' => $assignment->user->status->value,
                'assigned_at' => $assignment->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'data' => $mappedVolunteers,
            'pagination' => [
                'current_page' => $volunteers->currentPage(),
                'last_page' => $volunteers->lastPage(),
                'per_page' => $volunteers->perPage(),
                'total' => $volunteers->total(),
                'from' => $volunteers->firstItem(),
                'to' => $volunteers->lastItem(),
            ]
        ], 200);
    }

    /**
     * Self-assign to disaster (VOLUNTEER FOR THIS DISASTER button)
     */
    public function assignVolunteerToDisaster(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $user = auth('sanctum')->user();

        // Check if user is already assigned to this disaster
        $existingAssignment = DisasterVolunteer::where('disaster_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'You are already volunteering for this disaster.'
            ], 422);
        }

        $assignment = DisasterVolunteer::create([
            'disaster_id' => $id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Successfully volunteered for this disaster.',
            'data' => [
                'id' => $assignment->id,
                'disaster_id' => $assignment->disaster_id,
                'user_id' => $assignment->user_id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'assigned_at' => $assignment->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * Self-unassign from disaster (STOP VOLUNTEERING button)
     */
    public function removeVolunteerFromDisaster(Request $request, $id, $volunteerId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $user = auth('sanctum')->user();

        // Users can only remove themselves
        if ($volunteerId !== $user->id) {
            return response()->json([
                'message' => 'You can only remove yourself from volunteering.'
            ], 403);
        }

        $assignment = DisasterVolunteer::where('disaster_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'You are not volunteering for this disaster.'
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'message' => 'Successfully stopped volunteering for this disaster.'
        ], 200);
    }
}
