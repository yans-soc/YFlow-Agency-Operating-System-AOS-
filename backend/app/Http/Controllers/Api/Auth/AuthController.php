<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user with optional workspace creation
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $workspaceId = $request->input('workspace_id');
            $workspaceName = $request->input('workspace_name');

            DB::beginTransaction();

            // Create or use existing workspace
            if (!$workspaceId && $workspaceName) {
                $workspace = Workspace::create([
                    'name' => $workspaceName,
                    'slug' => $this->generateSlug($workspaceName),
                    'status' => 'active',
                ]);
                $workspaceId = $workspace->id;
            }

            // Create person
            $person = Person::create([
                'workspace_id' => $workspaceId,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'system_role' => 'workspace_admin',
                'status' => 'active',
            ]);

            $token = $person->createToken('auth-token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'person' => new PersonResource($person),
                    'token' => $token,
                ],
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 422);
        }
    }

    /**
     * Authenticate user and return token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $person = $request->authenticate();

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $person->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'person' => new PersonResource($person),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Refresh authentication token
     */
    public function refresh(LoginRequest $request): JsonResponse
    {
        $person = $request->authenticate();

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Delete old tokens
        $person->tokens()->delete();

        $token = $person->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'person' => new PersonResource($person),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(\Illuminate\Http\Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PersonResource($request->user()),
        ]);
    }

    /**
     * Logout user and revoke token
     */
    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Generate URL-friendly slug from name
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = preg_replace('/-+/', '-', $slug);
        $suffix = substr(md5(uniqid()), 0, 4);
        return "{$slug}-{$suffix}";
    }
}
