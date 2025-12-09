<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
     // Handle resetting the password
     public function reset(Request $request)
     {
         // Validate the request to ensure the token, email, and password are present
         $validator = Validator::make($request->all(), [
            'token' => 'required',
             'email' => 'required|email',
             'password' => 'required|confirmed|min:8', // Password confirmation and minimum length
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

         // Attempt to reset the password manually
         $response = Password::reset(
             $request->only('email', 'password', 'password_confirmation', 'token'),
             function ($user, $password) use ($request) {
                 // Hash the password using Hash::make()
                 $user->forceFill([
                     'password' => Hash::make($password), // Hash the new password
                 ])->save();
             }
         );
         // Log the response for debugging
        // Log::info('Password reset response: ' . $response);
         // Check if the password reset was successful and return appropriate response
        // Check for different responses and return appropriate messages
        switch ($response) {
            case Password::PASSWORD_RESET:
                // Successful password reset
                return response()->json(['status'=>'success','message' => 'Password has been reset successfully.']);

            case Password::INVALID_TOKEN:
                // Token is invalid (expired, tampered, or incorrect)
                return response()->json(['message' => 'This password reset token is invalid.'], 400);

            case Password::INVALID_USER:
                // User not found with the provided email
                return response()->json(['message' => 'We can\'t find a user with that email address.'], 404);

            default:
                // For any other unexpected error
                return response()->json(['message' => 'An unexpected error occurred. Please try again later.'], 500);
        }
     }
}
