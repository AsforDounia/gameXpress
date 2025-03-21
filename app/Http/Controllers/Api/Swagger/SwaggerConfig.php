<?php

namespace App\Http\Controllers\Api\Swagger;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="GameXpress API",
 *      description="API documentation for Order Management",
 *      @OA\Contact(
 *          email="support@gamexpress.com"
 *      )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     in="header"
 * )
 */

class SwaggerConfig extends Controller {}
