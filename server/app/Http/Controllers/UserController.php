<?php

namespace App\Http\Controllers;

// Importing request validation classes (custom FormRequests for validation rules)
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
// Importing the User model (represents the `users` table in the DB)
use App\Http\Resources\UserResources;
use App\Models\User;
// Importing response/request helpers from Laravel
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;



// UserController handles CRUD and profile-related actions for users
class UserController extends Controller
{
    /**
     * Display a paginated list of users.
     * Example: GET /api/v1/users?page=2
     */
    #[OA\Get(
        path: "/v1/users",
        security: [["bearerAuth" => []]],
        summary: "Get users list",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "page",
                description: "Page number",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
            ),
        ]
    )]

    public function index(): JsonResponse
    {
       Gate::authorize('view', User::class);

        $users = User::with('role')->paginate(10);

        return response()->json(
            UserResources::collection($users), 
            200
        );
    }

    /**
     * Show a single user by ID.
     * Example: GET /api/v1/users/5
     */

    #[OA\Get(
        path: "/v1/users/{id}",
        summary: "Show a single user by ID",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The unique identifier of the user",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User details retrieved successfully"
            ),
            new OA\Response(
                response: 404,
                description: "User not found"
            )
        ]
    )]

     public function show( int $id): JsonResponse
    {
        // define who can access to it 
         Gate::authorize('view', User::class);
        // Find user by primary key (id)
        $user = User::find($id);

        // If user doesn’t exist → return 404
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Otherwise return user data with 200 OK
        return response()->json(new UserResources($user), 200);
    }



    /**
     * Create a new user.
     * Example: GET /api/v1/users/
     */

    #[OA\Post(
        path: "/v1/users/",
        summary: "Create a new user",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref : '#/components/schemas/CreateUserRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created successfully"
            ),
        ]
    )]


    public function store(CreateUserRequest $request): JsonResponse
    {
           Gate::authorize('edit', User::class);
        // Get only allowed fields from request
        $data = $request->only(['f_name', 'l_name', 'email','password','role_id']);

        // Hash password before saving (security!)
        $data['password'] = Hash::make($request->input('password'));

        // Create new user in DB
        $user = User::create($data);

        // Return new user with 201 Created status
        return response()->json(new UserResources($user), 201);
    }

    /**
     * Update an existing user by ID.
     * Example: PUT /api/users/5
     */

    #[OA\Put(
        path: "/v1/users/{id}",
        summary: "Update a current user",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The unique identifier of the user",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref : '#/components/schemas/UpdateUserRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: "User Updated successfully"
            ),
        ]
    )]

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        // define who can access to it Admin | Editor
          Gate::authorize('edit', User::class);
        // Find the user
        $user = User::find($id);

        // If user not found → 404
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Collect fields to update (f_name, l_name, email)
        $data = $request->only(['f_name', 'l_name', 'email','role_id']);

        // If password field is present → hash it and include
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        // Update user in DB
        $user->update($data);

        // Return updated user with 202 Accepted
        return response()->json(new UserResources($user), 202);
    }

    /**
     * Delete a user by ID.
     * Example: DELETE /api/users/5
     */

        #[OA\Delete(
        path: "/v1/users/{id}",
        summary: "Delete a single user by ID",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The unique identifier of the user",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User Deleted successfully"
            ),
            new OA\Response(
                response: 404,
                description: "User not found"
            )
        ]
    )]

    public function destroy( int $id): JsonResponse
    {
        // define who can access to it Admin | Editor
          Gate::authorize('edit', User::class);
        // Find user
        $user = User::find($id);

        // If not found → 404
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Delete from DB
        $user->delete();

        // Return success message
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    /**
     * Get currently authenticated user
     * Example: GET /api/user (with token)
     */
    #[OA\Get(
        path:"/v1/profile",
        summary:"Get currently authenticated user",
        security:[["bearerAuth"=>[]]],
        tags:["User Profile"],
        responses:[
            new OA\Response(
                response:200,
                description:"User details retrieved successfully"
            ),
            new OA\Response(
                response:404,
                description:"User not found"
            )
        ]
        )
    ]
        public function user(): JsonResponse
            {
                $user = \Auth::user();
                
                // RETURN USER PERMISSIONS
                return (new UserResources($user))->additional([
                    'meta' => [
                        'permissions' => $user->permissions()
                    ]
                ])->response();
            }


    /**
     *  SWagger Docs
     * Example: PUT /api/user/info
     */
        #[OA\Put(
        path: "/v1/profile/info",
        summary: "Update a current user",
        security: [["bearerAuth" => []]],
        tags:["User Profile"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref : '#/components/schemas/ProfileUpdateRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: "User Updated successfully"
            ),
        ]
    )]

    /**
     * Update logged-in user’s profile info.
     * Example: PUT /api/user/info
     */

    public function updateInfo(UpdateUserRequest $request)
    {
        // 3. Obtain ONLY the validated fields
        $validated = $request->validated();

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data'    => $user
        ], 202);
    }


    /**
     *  SWagger Docs
     * Example: PUT /api/user/info
     */
        #[OA\Put(
        path: "/v1/profile/password",
        summary: "Update passwordd current user",
        security: [["bearerAuth" => []]],
        tags:["User Profile"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref : '#/components/schemas/ProfileUpdatePassword'
            )
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: "User Passsword Updated successfully"
            ),
        ]
    )]

    /**
     * Update logged-in user’s password.
     * Example: PUT /api/user/password
     */
    
    public function updatePassword(Request $request): JsonResponse
    {
        // Validate the new password
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get currently authenticated user
         $user = \Auth::user();

        // Hash the new password
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Return success message
        return response()->json([
            'message' => 'Password updated successfully',
            'user'    => new UserResources($user),
        ], 202);
    }
}
