<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     description="Authenticates a user and returns a JWT token if successful.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login details",
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="your_jwt_token_here"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="user_details", type="object", example="User Details")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or missing parameters",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The provided credentials are incorrect")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Email not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email not verified. Please verify your email.")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // $user = Auth::user();
            $user = User::where('email', $request->email)->first();
            // $user = User::where('email', $request->email)
            // ->where('is_deleted', 0)
            // ->with(['user_role' => function ($query) {
            //     // Specify the columns you want from the related 'user_role' table
            //     $query->select('id', 'role_name'); // Replace with actual column names
            // }])
            // ->select('*') // Specify the columns you want from the 'users' table
            // ->first();
             // Issue token with custom expiration time (e.g., 10 minutes)
            //  $token = $user->createToken('franchise', ['*'], now()->addMinutes(10))->plainTextToken;

            if (!$user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email not verified. Please verify your email.'], 401);
            }
            $token = $user->createToken('franchise')->plainTextToken;

            $result = [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user_details' => $user
            ];
            // return response()->json([
            //     'access_token' => $token,
            //     'token_type' => 'Bearer',
            //     'message' => 'Login successful.',
            // ]);
           return  $this->successResponse($result,'Login Successfull.');
        }else{
            return  $this->errorResponse('The provided credentials are incorrect',401);
        }
    }

    // Get the authenticated user
    public function user(Request $request)
    {
        return $this->successResponse($request->user());
        // return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        $user = User::where('id', $request->user_id)->first();
        if($user){
            return  $this->successResponse($user,'Logged out successfully.');
        }else{
            return  $this->errorResponse('The provided credentials are incorrect',401);
        }
    }
}
