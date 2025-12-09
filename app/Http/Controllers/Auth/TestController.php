<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="This is a simple API for demonstration purposes",
 *     termsOfService="http://example.com/terms",
 *     @OA\SecurityScheme(
 *          securityScheme="BearerAuth",
 *          type="http",
 *          scheme="bearer",
 *          description="Use the Bearer token to authenticate."
 *     ),
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 */

class TestController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/testing",
 *     summary="Test Swagger",
 *     description="A simple test endpoint to verify Swagger integration",
 *     tags={"Test"},
 *     @OA\Response(
 *         response=200,
 *         description="Test successful swagger",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Test successful swagger"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error"
 *     )
 * )
 */


    public function test()
    {
        return response()->json(['message' => 'Test successful swagger']);
    }
}
