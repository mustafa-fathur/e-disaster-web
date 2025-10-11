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
     * @OA\Get(
     *     path="/disasters/{id}/victims",
     *     summary="Get disaster victims",
     *     description="Get paginated list of victims for a specific disaster (assigned users only)",
     *     tags={"Victims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Disaster ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for name or description",
     *         required=false,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by victim status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"luka ringan","luka berat","meninggal","hilang"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Victims retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not assigned to disaster",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Access denied. You are not assigned to this disaster.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Disaster not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Disaster not found.")
     *         )
     *     )
     * )
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
    /**
     * @OA\Post(
     *     path="/disasters/{id}/victims",
     *     summary="Create disaster victim record",
     *     description="Create a new victim record for a specific disaster (assigned users only)",
     *     tags={"Victims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Disaster ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","status"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="age", type="integer", example=35),
     *             @OA\Property(property="gender", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", enum={"luka ringan","luka berat","meninggal","hilang"}, example="luka ringan"),
     *             @OA\Property(property="description", type="string", example="Victim found trapped under debris"),
     *             @OA\Property(property="location", type="string", example="Building A, Floor 2"),
     *             @OA\Property(property="lat", type="number", example=-6.2088),
     *             @OA\Property(property="long", type="number", example=106.8456)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Victim record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Disaster victim created successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not assigned to disaster",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Access denied. You are not assigned to this disaster.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
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
    /**
     * @OA\Get(
     *     path="/disasters/{id}/victims/{victimId}",
     *     summary="Get disaster victim details",
     *     description="Get detailed information about a specific disaster victim (assigned users only)",
     *     tags={"Victims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Disaster ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\Parameter(
     *         name="victimId",
     *         in="path",
     *         description="Victim ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f660")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Victim details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="age", type="integer"),
     *                 @OA\Property(property="gender", type="boolean"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="long", type="number"),
     *                 @OA\Property(property="pictures", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not assigned to disaster",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Access denied. You are not assigned to this disaster.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Victim not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Disaster victim not found.")
     *         )
     *     )
     * )
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
    /**
     * @OA\Put(
     *     path="/disasters/{id}/victims/{victimId}",
     *     summary="Update disaster victim record",
     *     description="Update a specific disaster victim record (assigned users only)",
     *     tags={"Victims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Disaster ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\Parameter(
     *         name="victimId",
     *         in="path",
     *         description="Victim ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f660")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="age", type="integer", example=36),
     *             @OA\Property(property="gender", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", enum={"luka ringan","luka berat","meninggal","hilang"}, example="luka berat"),
     *             @OA\Property(property="description", type="string", example="Updated victim status - now in stable condition"),
     *             @OA\Property(property="location", type="string", example="Hospital A, Room 205"),
     *             @OA\Property(property="lat", type="number", example=-6.2088),
     *             @OA\Property(property="long", type="number", example=106.8456)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Victim record updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Disaster victim updated successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not assigned to disaster",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Access denied. You are not assigned to this disaster.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Victim not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Disaster victim not found.")
     *         )
     *     )
     * )
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
    /**
     * @OA\Delete(
     *     path="/disasters/{id}/victims/{victimId}",
     *     summary="Delete disaster victim record",
     *     description="Delete a specific disaster victim record (assigned users only)",
     *     tags={"Victims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Disaster ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\Parameter(
     *         name="victimId",
     *         in="path",
     *         description="Victim ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f660")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Victim record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Disaster victim deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not assigned to disaster",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Access denied. You are not assigned to this disaster.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Victim not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Disaster victim not found.")
     *         )
     *     )
     * )
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