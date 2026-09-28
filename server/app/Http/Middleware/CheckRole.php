<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
        public function handle(Request $request, Closure $next, string ...$roles): Response
        {
            $user = $request->user();

            if (!$user || !$user->role) {
                return response()->json([
                    'message' => 'Forbidden. You do not have permission to access this resource.',
                ], Response::HTTP_FORBIDDEN);
            }

            // Force everything to lowercase to prevent capitalization mismatches
            $userRoleStr = strtolower(is_object($user->role) ? $user->role->name : $user->role);
            $allowedRoles = array_map('strtolower', $roles);

            if (!in_array($userRoleStr, $allowedRoles)) {
                return response()->json([
                    'message' => 'Forbidden. You do not have permission to access this resource.',
                ], Response::HTTP_FORBIDDEN);
            }

            return $next($request);
        }
}