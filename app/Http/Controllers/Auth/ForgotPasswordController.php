<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $getUser = User::where('email',$request->email)->first();

        if($getUser){
            $response = Password::sendResetLink($request->only('email'),
                function ($user, $token) use ($request) {
                    // Send custom email with the PasswordResetMail using Mail::to()
                    Mail::to($user->email)->send(new PasswordResetMail($token, $user->email)); // Corrected this line
                }
            );
        }else{
            return response()->json(['message' => 'User Not Found'], 400);
        }


        return $response == Password::RESET_LINK_SENT
            ? response()->json(['status'=>'success','message' => 'Password reset link sent','data'=>$response], 200)
            : response()->json(['message' => 'Failed to send reset link or Already Sent'], 400);
    }
}
