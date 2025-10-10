<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\DisasterReport;
use App\Models\DisasterVolunteer;
use App\Models\Picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DisasterReportController extends Controller
{
    /**
     * Get all reports for a specific disaster
     */
    public function getDisasterReports(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = DisasterReport::where('disaster_id', $id)
            ->with(['disaster', 'reporter.user']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $reportData = $reports->items();
        $mappedReports = collect($reportData)->map(function ($report) {
            return [
                'id' => $report->id,
                'disaster_id' => $report->disaster_id,
                'disaster_title' => $report->disaster->title,
                'title' => $report->title,
                'description' => $report->description,
                'is_final_stage' => $report->is_final_stage,
                'reported_by' => $report->reported_by,
                'reporter_name' => $report->reporter->user->name ?? 'Unknown',
                'created_at' => $report->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $report->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'data' => $mappedReports,
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'from' => $reports->firstItem(),
                'to' => $reports->lastItem(),
            ]
        ], 200);
    }

    /**
     * Create new disaster report
     */
    public function createDisasterReport(Request $request, $id)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:45',
            'description' => 'required|string',
            'is_final_stage' => 'nullable|boolean',
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

        $report = DisasterReport::create([
            'disaster_id' => $id,
            'title' => $request->title,
            'description' => $request->description,
            'is_final_stage' => $request->is_final_stage ?? false,
            'reported_by' => $disasterVolunteer->id, // Reference to disaster_volunteers table
        ]);

        // If this is a final stage report, update disaster status to completed
        if ($request->is_final_stage) {
            $disaster->update([
                'status' => \App\Enums\DisasterStatusEnum::COMPLETED,
                'completed_at' => now(),
                'completed_by' => $disasterVolunteer->id
            ]);
        }

        return response()->json([
            'message' => 'Disaster report created successfully.',
            'data' => [
                'id' => $report->id,
                'disaster_id' => $report->disaster_id,
                'title' => $report->title,
                'description' => $report->description,
                'is_final_stage' => $report->is_final_stage,
                'reported_by' => $report->reported_by,
                'created_at' => $report->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * Get specific disaster report
     */
    public function getDisasterReport(Request $request, $id, $reportId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $report = DisasterReport::where('disaster_id', $id)
            ->where('id', $reportId)
            ->with(['disaster', 'reporter.user'])
            ->first();

        if (!$report) {
            return response()->json([
                'message' => 'Disaster report not found.'
            ], 404);
        }

        // Get pictures for this report
        $pictures = Picture::where('foreign_id', $report->id)
            ->where('type', 'report')
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
                'id' => $report->id,
                'disaster_id' => $report->disaster_id,
                'disaster_title' => $report->disaster->title,
                'title' => $report->title,
                'description' => $report->description,
                'is_final_stage' => $report->is_final_stage,
                'reported_by' => $report->reported_by,
                'reporter_name' => $report->reporter->user->name ?? 'Unknown',
                'pictures' => $pictures,
                'created_at' => $report->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $report->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Update disaster report
     */
    public function updateDisasterReport(Request $request, $id, $reportId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $report = DisasterReport::where('disaster_id', $id)
            ->where('id', $reportId)
            ->first();

        if (!$report) {
            return response()->json([
                'message' => 'Disaster report not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:45',
            'description' => 'sometimes|required|string',
            'is_final_stage' => 'nullable|boolean',
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

        $updateData = $request->only(['title', 'description', 'is_final_stage']);
        $report->update($updateData);

        // If this is a final stage report, update disaster status to completed
        if ($request->has('is_final_stage') && $request->is_final_stage) {
            $disaster->update([
                'status' => \App\Enums\DisasterStatusEnum::COMPLETED,
                'completed_at' => now(),
                'completed_by' => $disasterVolunteer->id
            ]);
        }

        return response()->json([
            'message' => 'Disaster report updated successfully.',
            'data' => [
                'id' => $report->id,
                'disaster_id' => $report->disaster_id,
                'title' => $report->title,
                'description' => $report->description,
                'is_final_stage' => $report->is_final_stage,
                'updated_at' => $report->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Delete disaster report
     */
    public function deleteDisasterReport(Request $request, $id, $reportId)
    {
        $disaster = Disaster::find($id);

        if (!$disaster) {
            return response()->json([
                'message' => 'Disaster not found.'
            ], 404);
        }

        $report = DisasterReport::where('disaster_id', $id)
            ->where('id', $reportId)
            ->first();

        if (!$report) {
            return response()->json([
                'message' => 'Disaster report not found.'
            ], 404);
        }

        $report->delete();

        return response()->json([
            'message' => 'Disaster report deleted successfully.'
        ], 200);
    }
}
