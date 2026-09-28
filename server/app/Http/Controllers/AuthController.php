<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /** Authenticate a user and issue an access token. */
    #[OA\Post(
        path: '/v1/login',
        summary: 'Log in a user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 401, description: 'Invalid credentials'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        // Validate request
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        
        // Attempt to login
        if (Auth::attempt($request->only('email', 'password'))) {
            // Get authenticated user and load role relationship
            $user  = Auth::user()->load('role');
            // Generate token and set cookie
            $token = $user->createToken('admin')->accessToken;
            // Set cookie
            $cookie = cookie('jwt', $token, 60 * 24);
            // Return response with token and user data
            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'        => $user->id,
                    'full_name' => trim("{$user->f_name} {$user->l_name}"),
                    'email'     => $user->email,
                    'role'      => $user->role?->name,
                ],
            ])->withCookie($cookie);
        }

        return response()->json(
            ['error' => 'Invalid credentials'],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /** Register a new user and issue an access token. */
    #[OA\Post(
        path: '/v1/register',
        summary: 'Register a user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['f_name', 'l_name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'f_name', type: 'string'),
                    new OA\Property(property: 'l_name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User registered successfully'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->only(['f_name', 'l_name', 'email']);
        $data['password'] = Hash::make($request->input('password'));

        // Find default role or assign fallback ID (e.g. 2 for standard user)
        $defaultRole = Role::where('name', 'user')->first();
        $data['role_id'] = $defaultRole ? $defaultRole->id : 3;

        $user = User::create($data);
        $token = $user->createToken('admin')->accessToken;

        return response()->json([
            'message' => 'User created successfully',
            'token'   => $token,
            'user'    => [
                'id'        => $user->id,
                'full_name' => trim("{$user->f_name} {$user->l_name}"),
                'email'     => $user->email,
                'role'      => optional($user->role)->name,
            ],
        ], Response::HTTP_CREATED);
    }

    /** Revoke the current session cookie. */
    #[OA\Post(
        path: '/v1/logout',
        summary: 'Log out the current user',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Logout successful'),
        ]
    )]
    public function logout(): JsonResponse
    {
        $cookie = \Cookie::forget('jwt');

        return response()->json([
                'message' => 'Successfully logged out',
        ])->withCookie($cookie);
    }
}
