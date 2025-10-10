<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserTypeEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            ]
        ], 200);
    }
}
