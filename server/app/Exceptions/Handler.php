<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // 1. Handle Validation Exceptions
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            }
        });

        // 2. Handle Unauthenticated / Missing Tokens
        $this->renderable(function (AuthenticationException|OAuthServerException $e, $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthenticated.'
                ], Response::HTTP_UNAUTHORIZED); // 401
            }
        });

        // 3. Catch-all for other throwables (only for API requests)
        $this->renderable(function (Throwable $e, $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                $code = $e instanceof HttpExceptionInterface 
                    ? $e->getStatusCode() 
                    : Response::HTTP_INTERNAL_SERVER_ERROR; // Default to 500 for unhandled exceptions

                return response()->json([
                    'error' => $e->getMessage(),
                ], $code);
            }
        });
    }
}