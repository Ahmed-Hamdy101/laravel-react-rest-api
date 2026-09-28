<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "My API",
    description: "API description"
)]
#[OA\Contact(
    email: "ahmedhamdy.mh95@gmail.com"
)]
#[OA\License(
    name: "Apache 2.0",
    url: "https://www.apache.org/licenses/LICENSE-2.0.html"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Local server"
)]
// Security scheme for JWT authentication if not added, authentication will not be documented in the OpenAPI spec
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]


class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
