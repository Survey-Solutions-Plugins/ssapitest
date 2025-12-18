<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login - Authenticate user and generate token
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'email' => 'sometimes|email',
            'username' => 'sometimes|string'
        ]);

        // Accept either email or username for login, prioritizing email if provided
        $user = null;
        if ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->filled('username')) {
            $user = User::where('username', $request->username)->first();
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if ($user->is_archived) {
            return response()->json([
                'message' => 'User account is archived'
            ], 403);
        }

        if ($user->is_locked) {
            return response()->json([
                'message' => 'User account is locked'
            ], 403);
        }

        $token = $user->createToken('survey-solutions-api')->plainTextToken;

        return response()->json([
            'user' => $user->load(['supervisor.workspace', 'interviewer.supervisor.user', 'interviewer.workspace']),
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * POST /api/v1/auth/logout - Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * GET /api/v1/auth/me - Get authenticated user information
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['supervisor.workspace', 'interviewer.supervisor.user', 'interviewer.workspace'])
        ]);
    }

    /**
     * POST /api/v1/auth/refresh - Refresh authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        
        $token = $user->createToken('survey-solutions-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
