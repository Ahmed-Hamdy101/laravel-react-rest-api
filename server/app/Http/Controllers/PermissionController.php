<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Http\Requests\CreatePermissionRequset;
use OpenApi\Attributes as OA;

class PermissionController extends Controller
{
        /** List permissions with pagination. */
        #[OA\Get(
            path: '/v1/permissions',
            summary: 'List permissions',
            security: [['bearerAuth' => []]],
            tags: ['Permissions'],
            responses: [new OA\Response(response: 200, description: 'Permissions retrieved successfully')]
        )]
        public function index()
        {
            // Use pagination to keep memory usage constant regardless of table size
            return PermissionResource::collection(Permission::paginate(20));
        }

                /** Create a permission from validated request data. */
                #[OA\Post(
                        path: '/v1/permissions',
                        summary: 'Create a permission',
                        security: [['bearerAuth' => []]],
                        tags: ['Permissions'],
                        responses: [
                                new OA\Response(response: 201, description: 'Permission created successfully'),
                                new OA\Response(response: 422, description: 'Validation failed'),
                        ]
                )]
                public function store(CreatePermissionRequset $request)
        {
                // alidated data is automatically returned by the FormRequest
                $validated = $request->validated();

                //  Create the permission
                $permission = Permission::create($validated);

                //  Return the newly created permission using your API Resource
                // This ensures your JSON response format is consistent
                return new PermissionResource($permission);
        }
}