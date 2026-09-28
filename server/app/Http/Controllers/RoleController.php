<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use App\Http\Resources\RoleResources;
use App\Models\Role;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;
class RoleController extends Controller
{
    /** List available roles with their pagination metadata. */
    #[OA\Get(
        path: '/v1/roles',
        summary: 'List roles',
        security: [['bearerAuth' => []]],
        tags: ['Roles'],
        responses: [new OA\Response(response: 200, description: 'Roles retrieved successfully')]
    )]
    public function index()
    {
        Gate::authorize('view', Role::class);
        return RoleResources::collection(Role::paginate(10));
    }

    /** Create a role and optionally assign permissions. */
    #[OA\Post(
        path: '/v1/roles',
        summary: 'Create a role',
        security: [['bearerAuth' => []]],
        tags: ['Roles'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'integer')),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Role created successfully')]
    )]
        public function store(CreateRoleRequest $request)
        {
            Gate::authorize('edit', Role::class);
            return \DB::transaction(function () use ($request) {
                // 1. Create the role
                $role = Role::create([
                    'name' => $request->validated('name'),
                ]);

                // 2. Sync the permissions
                $permissions = $request->input('permissions', []);
                $role->permissions()->sync($permissions);

                // 3. Load the permissions relationship so the Resource can show them
                $role->load('permissions');

                // 4. Return as a JSON Resource with 201 Created status
                return (new RoleResources($role))
                    ->response()
                    ->setStatusCode(201);
            });
        }

    /** Show one role by ID. */
    #[OA\Get(
        path: '/v1/roles/{id}',
        summary: 'Show a role',
        security: [['bearerAuth' => []]],
        tags: ['Roles'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Role retrieved successfully')]
    )]
    public function show(string $id)
    {
        Gate::authorize('view', Role::class);
        return new RoleResources(Role::findOrFail($id));
    }

    /** Update a role and optionally replace its permissions. */
    #[OA\Put(
        path: '/v1/roles/{id}',
        summary: 'Update a role',
        security: [['bearerAuth' => []]],
        tags: ['Roles'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Role updated successfully')]
    )]
        public function update(Request $request, string $id)
        {
            Gate::authorize('edit', Role::class);
            $role = Role::findOrFail($id);

            // 1. Update the role name
            $role->update($request->only('name'));

            // 2. Sync permissions (Replaces the delete and the loop!)
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->input('permissions'));
            }

            return response()->json(new RoleResources($role->load('permissions')), Response::HTTP_OK);
        }

    /** Delete a role by ID. */
    #[OA\Delete(
        path: '/v1/roles/{id}',
        summary: 'Delete a role',
        security: [['bearerAuth' => []]],
        tags: ['Roles'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Role deleted successfully')]
    )]
    public function destroy(string $id)
    {
        Gate::authorize('edit', Role::class);
        \DB::table('role_permissions')->where('role_id',$id)->delete(); 
        Role::destroy($id); 
        return response()->json(['message' => 'Role deleted successfully'], Response::HTTP_OK);
    }
}
