<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Picture;
use App\Enums\UserTypeEnum;
use App\Enums\UserStatusEnum;
use App\Enums\PictureTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }   

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user is active
        if ($user->status !== UserStatusEnum::ACTIVE) {
            return response()->json([
                'message' => 'Account is not active. Please contact administrator.'
            ], 403);
        }

        // Create token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type->value,
                'status' => $user->status->value,
            ],
            'token' => $token
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nik' => 'required|string|max:45|unique:users',
            'phone' => 'required|string|max:45',
            'address' => 'required|string',
            'gender' => 'required|boolean',
            'date_of_birth' => 'required|date|before:today',
            'reason_to_join' => 'nullable|string|max:245',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => UserTypeEnum::VOLUNTEER,
            'status' => UserStatusEnum::REGISTERED,
            'email_verified' => false,
            'nik' => $request->nik,
            'phone' => $request->phone,
            'address' => $request->address,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'reason_to_join' => $request->reason_to_join,
            'registered_at' => now(),
        ]);

        return response()->json([
            'message' => 'Registration successful. Please wait for admin approval.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type->value,
                'status' => $user->status->value,
                'nik' => $user->nik,
                'phone' => $user->phone,
                'address' => $user->address,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth->format('Y-m-d'),
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        auth('sanctum')->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function me(Request $request)
    {
        $user = auth('sanctum')->user();
        
        // Get profile picture
        $profilePicture = Picture::where('foreign_id', $user->id)
            ->where('type', PictureTypeEnum::PROFILE)
            ->first();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type->value,
                'status' => $user->status->value,
                'email_verified' => $user->email_verified,
                'nik' => $user->nik,
                'phone' => $user->phone,
                'address' => $user->address,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'reason_to_join' => $user->reason_to_join,
                'registered_at' => $user->registered_at?->format('Y-m-d H:i:s'),
                'approved_at' => $user->approved_at?->format('Y-m-d H:i:s'),
                'timezone' => $user->timezone,
                'location' => $user->location,
                'lat' => $user->lat,
                'long' => $user->long,
                'profile_picture' => $profilePicture ? [
                    'id' => $profilePicture->id,
                    'url' => Storage::url($profilePicture->file_path),
                    'caption' => $profilePicture->caption,
                    'alt_text' => $profilePicture->alt_text,
                ] : null,
            ]
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'nik' => 'sometimes|required|string|max:45|unique:users,nik,' . $user->id,
            'phone' => 'sometimes|required|string|max:45',
            'address' => 'sometimes|required|string',
            'gender' => 'sometimes|required|boolean',
            'date_of_birth' => 'sometimes|required|date|before:today',
            'reason_to_join' => 'sometimes|nullable|string|max:245',
            'timezone' => 'sometimes|nullable|string|max:50',
            'location' => 'sometimes|nullable|string|max:255',
            'lat' => 'sometimes|nullable|numeric|between:-90,90',
            'long' => 'sometimes|nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user profile
        $user->update($validator->validated());

        // Get updated profile picture
        $profilePicture = Picture::where('foreign_id', $user->id)
            ->where('type', PictureTypeEnum::PROFILE)
            ->first();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type->value,
                'status' => $user->status->value,
                'email_verified' => $user->email_verified,
                'nik' => $user->nik,
                'phone' => $user->phone,
                'address' => $user->address,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'reason_to_join' => $user->reason_to_join,
                'registered_at' => $user->registered_at?->format('Y-m-d H:i:s'),
                'approved_at' => $user->approved_at?->format('Y-m-d H:i:s'),
                'timezone' => $user->timezone,
                'location' => $user->location,
                'lat' => $user->lat,
                'long' => $user->long,
                'profile_picture' => $profilePicture ? [
                    'id' => $profilePicture->id,
                    'url' => Storage::url($profilePicture->file_path),
                    'caption' => $profilePicture->caption,
                    'alt_text' => $profilePicture->alt_text,
                ] : null,
            ]
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('sanctum')->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }

    public function updateProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            'caption' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('sanctum')->user();

        // Handle file upload
        $file = $request->file('image');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('pictures/profile', $fileName, 'public');

        // Delete existing profile picture if exists
        $existingPicture = Picture::where('foreign_id', $user->id)
            ->where('type', PictureTypeEnum::PROFILE)
            ->first();

        if ($existingPicture) {
            Storage::disk('public')->delete($existingPicture->file_path);
            $existingPicture->delete();
        }

        // Create new profile picture record
        $picture = Picture::create([
            'foreign_id' => $user->id,
            'type' => PictureTypeEnum::PROFILE,
            'caption' => $request->caption,
            'file_path' => $filePath,
            'mine_type' => $file->getMimeType(),
            'alt_text' => $request->alt_text,
        ]);

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'data' => [
                'id' => $picture->id,
                'url' => Storage::url($picture->file_path),
                'caption' => $picture->caption,
                'alt_text' => $picture->alt_text,
                'created_at' => $picture->created_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    public function deleteProfilePicture(Request $request)
    {
        $user = auth('sanctum')->user();

        $picture = Picture::where('foreign_id', $user->id)
            ->where('type', PictureTypeEnum::PROFILE)
            ->first();

        if (!$picture) {
            return response()->json([
                'message' => 'Profile picture not found'
            ], 404);
        }

        Storage::disk('public')->delete($picture->file_path);
        $picture->delete();

        return response()->json([
            'message' => 'Profile picture deleted successfully'
        ], 200);
    }
}
