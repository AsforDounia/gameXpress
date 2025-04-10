<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/admin/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */

    public function register(Request $request)
    {

        $fields = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);
        $user = User::create($fields);

        if (User::count() === 1) {
            $superAdminRole = Role::where('name', 'super_admin')->where('guard_name', 'sanctum')->first();
            if ($superAdminRole) {
                $user->roles()->attach($superAdminRole);
            }
            // $user->assignRole(['super_admin', 'sanctum']);
            // $user->assignRole('super_admin', 'sanctum');
            // $user->assignRole('super_admin');
        } else {
            $clientRole = Role::where('name', 'client')->where('guard_name', 'sanctum')->first();
            if ($clientRole) {
                $user->roles()->attach($clientRole);
            }
            // $user->assignRole(['client', 'sanctum']);
            // $user->assignRole('client','sanctum');
        }

        $token = $user->createToken($request->name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User logged in successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string", example="token_here")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken($user->name);

        return [
            'status' => 'success',
            'message' => 'User logged in successfully',
            'data' => [
                'user' => $user,
                'token' => $token->plainTextToken
            ]
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/logout",
     *     summary="User logout",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function logout(Request $request)
    {

        $request->user()->tokens()->delete();
        return [
            'status' => 'success',
            'message' => 'User logged out successfully'
        ];
    }

    public function usertest()
    {
        $user = auth()->user();;
        $roles = $user->roles()->pluck('name');

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json([
            'user' => $user,
            'roles' => $roles,
        ]);
    }
}
