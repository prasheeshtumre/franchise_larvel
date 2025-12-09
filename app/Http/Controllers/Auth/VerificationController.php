<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class VerificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/verify/{id}/{hash}",
     *     summary="Verify user's email",
     *     description="Verify the user's email address by checking the hash. If the email is already verified, return a message. If verification is successful, log the user in and return an authentication token.",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="Hashed email for verification",
     *         @OA\Schema(type="string", example="hash_value_here")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email successfully verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email successfully verified"),
     *             @OA\Property(property="data", example="Data"),
     *             @OA\Property(property="token", type="string", example="Bearer token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification link or failed verification",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid verification link.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Email already verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="verified"),
     *             @OA\Property(property="message", type="string", example="Email already verified"),
     *             @OA\Property(property="data", example="Data")
     *         )
     *     )
     * )
     */
    public function verify(Request $request, $id,$hash)
    {
        $user = User::findOrFail($id);

        // Check if the hash matches the user email
        if (sha1($user->email) !== $hash) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        // Verify the email
        if ($user->hasVerifiedEmail()) {
            // $user = Auth::login($user);
            return response()->json(['message' => 'Email already verified','status' => 'verified','data'=>$user], 201);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user)); // Optionally trigger the Verified event

            // Log the user in programmatically
            Auth::login($user);
            $token = $user->createToken('verification')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Email successfully verified',
                'data'=>$user,
                'token' =>  $token
            ], 200);
        }

        return response()->json(['message' => 'Verification failed'], 400);
    }

    public function resend(Request $request)
    {
        // $user = Auth::user(); // Use the authenticated user
        // $user = User::get(); // Use the authenticated user

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $getUser = User::where('email',$request->email)->where('is_deleted',0)->first();
        // Resend the verification email
        if($getUser){
            // Check if the email is already verified
            if ($getUser->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified.'], 401);
            }
            $getUser->sendEmailVerificationNotification();
            return response()->json([
                'status' => 'success',
                'message' => 'Verification link sent successfully! Please check your email for the verification link.',
                'data' => $getUser,
            ], 201);
        }else{
            return response()->json(['error' => 'User not found','message'=>'User Not Found'], 401);
        }
    }
}
