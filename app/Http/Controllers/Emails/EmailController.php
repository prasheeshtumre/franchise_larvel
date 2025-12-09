<?php

namespace App\Http\Controllers\Emails;

use App\Http\Controllers\Controller;
use App\Mail\CustomEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmail($data){


        // Get the data from the request
        $name = $data['name'];
        $toEmail = $data['to_email'];
        $subject = $data['subject'];
        $fromEmail = $data['from_email'];
        $fromName = $data['from_name'];
        // $reset_link = isset($data['reset_link']);
        $emailType = $data['email_type'];  // Get the email type

        // Prepare dynamic email content based on the email type
        switch ($emailType) {
            case 'welcome':
                $view = 'emails.welcome';
                $data = ['data' => $data];  // For the welcome email, pass the name
                break;
            case 'password_reset':
                $view = 'emails.password_reset';
                $data = ['resetLink' => $data];  // Pass the reset link
                break;
            case 'custom_notification':
                $view = 'emails.custom_notification';
                $data = ['notificationMessage' => $data];  // Custom notification with a dynamic message
                break;
            default:
                return response()->json(['error' => 'Invalid email type.'], 400);
        }

         // Send the email using the selected Blade template and pass dynamic data
         Mail::send($view, $data, function ($message) use ($toEmail, $subject, $fromEmail, $fromName) {
            $message->to($toEmail)
                    ->subject($subject)
                    ->from($fromEmail, $fromName);
        });

        return response()->json(['message' => 'Email sent successfully!']);
    }
}
