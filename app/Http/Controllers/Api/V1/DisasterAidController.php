<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\DisasterAid;
use App\Models\DisasterVolunteer;
use App\Models\Picture;
use App\Enums\DisasterAidCategoryEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DisasterAidController extends Controller
{
    /**
     * Get all aids for a specific disaster
     */
    public function getDisasterAids(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = DisasterAid::where('disaster_id', $id)
            ->with(['disaster', 'reporter.user']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $aids = $query->paginate($perPage);

        return response()->json([
            'data' => $aids->items(),
            'pagination' => [
                'current_page' => $aids->currentPage(),
                'per_page' => $aids->perPage(),
                'total' => $aids->total(),
                'last_page' => $aids->lastPage(),
                'from' => $aids->firstItem(),
                'to' => $aids->lastItem(),
            ]
        ], 200);
    }

    /**
     * Create new disaster aid
     */
    public function createDisasterAid(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:45',
            'description' => 'nullable|string',
            'category' => 'required|in:food,clothing,housing',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:45',
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

        $aid = DisasterAid::create([
            'disaster_id' => $id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => DisasterAidCategoryEnum::from($request->category),
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'reported_by' => $disasterVolunteer->id,
        ]);

        return response()->json([
            'message' => 'Disaster aid created successfully.',
            'data' => [
                'id' => $aid->id,
                'disaster_id' => $aid->disaster_id,
                'title' => $aid->title,
                'description' => $aid->description,
                'category' => $aid->category->value,
                'quantity' => $aid->quantity,
                'unit' => $aid->unit,
                'reported_by' => $aid->reported_by,
                'created_at' => $aid->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * Get specific disaster aid
     */
    public function getDisasterAid(Request $request, $id, $aidId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $aid = DisasterAid::where('disaster_id', $id)
            ->where('id', $aidId)
            ->with(['disaster', 'reporter.user'])
            ->first();

        if (!$aid) {
            return response()->json([
                'message' => 'Disaster aid not found.'
            ], 404);
        }

        // Get pictures for this aid
        $pictures = Picture::where('foreign_id', $aid->id)
            ->where('type', 'aid')
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
                'id' => $aid->id,
                'disaster_id' => $aid->disaster_id,
                'disaster_title' => $aid->disaster->title,
                'title' => $aid->title,
                'description' => $aid->description,
                'category' => $aid->category->value,
                'quantity' => $aid->quantity,
                'unit' => $aid->unit,
                'reported_by' => $aid->reported_by,
                'reporter_name' => $aid->reporter->user->name ?? 'Unknown',
                'pictures' => $pictures,
                'created_at' => $aid->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $aid->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Update disaster aid
     */
    public function updateDisasterAid(Request $request, $id, $aidId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $aid = DisasterAid::where('disaster_id', $id)
            ->where('id', $aidId)
            ->first();

        if (!$aid) {
            return response()->json([
                'message' => 'Disaster aid not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:45',
            'description' => 'nullable|string',
            'category' => 'sometimes|required|in:food,clothing,housing',
            'quantity' => 'sometimes|required|integer|min:1',
            'unit' => 'sometimes|required|string|max:45',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'title', 'description', 'quantity', 'unit'
        ]);

        // Handle enum field
        if ($request->has('category')) {
            $updateData['category'] = DisasterAidCategoryEnum::from($request->category);
        }

        $aid->update($updateData);

        return response()->json([
            'message' => 'Disaster aid updated successfully.',
            'data' => [
                'id' => $aid->id,
                'disaster_id' => $aid->disaster_id,
                'title' => $aid->title,
                'description' => $aid->description,
                'category' => $aid->category->value,
                'quantity' => $aid->quantity,
                'unit' => $aid->unit,
                'updated_at' => $aid->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Delete disaster aid
     */
    public function deleteDisasterAid(Request $request, $id, $aidId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $aid = DisasterAid::where('disaster_id', $id)
            ->where('id', $aidId)
            ->first();

        if (!$aid) {
            return response()->json([
                'message' => 'Disaster aid not found.'
            ], 404);
        }

        $aid->delete();

        return response()->json([
            'message' => 'Disaster aid deleted successfully.'
        ], 200);
    }
}