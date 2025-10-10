<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\DisasterVictim;
use App\Models\DisasterVolunteer;
use App\Models\Picture;
use App\Enums\DisasterVictimStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DisasterVictimController extends Controller
{
    /**
     * Get all victims for a specific disaster
     */
    public function getDisasterVictims(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = DisasterVictim::where('disaster_id', $id)
            ->with(['disaster', 'reporter.user']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('contact_info', 'like', "%{$search}%");
            });
        }

        $victims = $query->paginate($perPage);

        return response()->json([
            'data' => $victims->items(),
            'pagination' => [
                'current_page' => $victims->currentPage(),
                'per_page' => $victims->perPage(),
                'total' => $victims->total(),
                'last_page' => $victims->lastPage(),
                'from' => $victims->firstItem(),
                'to' => $victims->lastItem(),
            ]
        ], 200);
    }

    /**
     * Create new disaster victim
     */
    public function createDisasterVictim(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:45',
            'name' => 'required|string|max:45',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|boolean',
            'contact_info' => 'nullable|string|max:45',
            'description' => 'nullable|string',
            'is_evacuated' => 'nullable|boolean',
            'status' => 'nullable|in:minor_injury,serious_injuries,deceased,missing',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('sanctum')->user();

        // Get the disaster volunteer assignment for this user
        $disasterVolunteer = DisasterVolunteer::where('disaster_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$disasterVolunteer) {
            return response()->json([
                'message' => 'You are not assigned to this disaster.'
            ], 403);
        }

        $victim = DisasterVictim::create([
            'disaster_id' => $id,
            'nik' => $request->nik,
            'name' => $request->name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'contact_info' => $request->contact_info,
            'description' => $request->description,
            'is_evacuated' => $request->is_evacuated ?? false,
            'status' => $request->status ?? DisasterVictimStatusEnum::MINOR_INJURY,
            'reported_by' => $disasterVolunteer->id,
        ]);

        return response()->json([
            'message' => 'Disaster victim created successfully.',
            'data' => [
                'id' => $victim->id,
                'disaster_id' => $victim->disaster_id,
                'nik' => $victim->nik,
                'name' => $victim->name,
                'date_of_birth' => $victim->date_of_birth->format('Y-m-d'),
                'gender' => $victim->gender,
                'contact_info' => $victim->contact_info,
                'description' => $victim->description,
                'is_evacuated' => $victim->is_evacuated,
                'status' => $victim->status->value,
                'reported_by' => $victim->reported_by,
                'created_at' => $victim->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * Get specific disaster victim
     */
    public function getDisasterVictim(Request $request, $id, $victimId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $victim = DisasterVictim::where('disaster_id', $id)
            ->where('id', $victimId)
            ->with(['disaster', 'reporter.user'])
            ->first();

        if (!$victim) {
            return response()->json([
                'message' => 'Disaster victim not found.'
            ], 404);
        }

        // Get pictures for this victim
        $pictures = Picture::where('foreign_id', $victim->id)
            ->where('type', 'victim')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($picture) {
                return [
                    'id' => $picture->id,
                    'caption' => $picture->caption,
                    'file_path' => $picture->file_path,
                    'url' => \Illuminate\Support\Facades\Storage::url($picture->file_path),
                    'mine_type' => $picture->mine_type,
                    'alt_text' => $picture->alt_text,
                    'created_at' => $picture->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'data' => [
                'id' => $victim->id,
                'disaster_id' => $victim->disaster_id,
                'disaster_title' => $victim->disaster->title,
                'nik' => $victim->nik,
                'name' => $victim->name,
                'date_of_birth' => $victim->date_of_birth->format('Y-m-d'),
                'gender' => $victim->gender,
                'contact_info' => $victim->contact_info,
                'description' => $victim->description,
                'is_evacuated' => $victim->is_evacuated,
                'status' => $victim->status->value,
                'reported_by' => $victim->reported_by,
                'reporter_name' => $victim->reporter->user->name ?? 'Unknown',
                'pictures' => $pictures,
                'created_at' => $victim->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $victim->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Update disaster victim
     */
    public function updateDisasterVictim(Request $request, $id, $victimId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $victim = DisasterVictim::where('disaster_id', $id)
            ->where('id', $victimId)
            ->first();

        if (!$victim) {
            return response()->json([
                'message' => 'Disaster victim not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'sometimes|required|string|max:45',
            'name' => 'sometimes|required|string|max:45',
            'date_of_birth' => 'sometimes|required|date|before:today',
            'gender' => 'sometimes|required|boolean',
            'contact_info' => 'nullable|string|max:45',
            'description' => 'nullable|string',
            'is_evacuated' => 'nullable|boolean',
            'status' => 'sometimes|required|in:minor_injury,serious_injuries,deceased,missing',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'nik', 'name', 'date_of_birth', 'gender', 'contact_info', 
            'description', 'is_evacuated', 'status'
        ]);

        // Handle enum field
        if ($request->has('status')) {
            $updateData['status'] = DisasterVictimStatusEnum::from($request->status);
        }

        $victim->update($updateData);

        return response()->json([
            'message' => 'Disaster victim updated successfully.',
            'data' => [
                'id' => $victim->id,
                'disaster_id' => $victim->disaster_id,
                'nik' => $victim->nik,
                'name' => $victim->name,
                'date_of_birth' => $victim->date_of_birth->format('Y-m-d'),
                'gender' => $victim->gender,
                'contact_info' => $victim->contact_info,
                'description' => $victim->description,
                'is_evacuated' => $victim->is_evacuated,
                'status' => $victim->status->value,
                'updated_at' => $victim->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Delete disaster victim
     */
    public function deleteDisasterVictim(Request $request, $id, $victimId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $victim = DisasterVictim::where('disaster_id', $id)
            ->where('id', $victimId)
            ->first();

        if (!$victim) {
            return response()->json([
                'message' => 'Disaster victim not found.'
            ], 404);
        }

        $victim->delete();

        return response()->json([
            'message' => 'Disaster victim deleted successfully.'
        ], 200);
    }
}