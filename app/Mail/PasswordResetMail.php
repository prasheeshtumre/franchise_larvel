<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        // return $this->subject('Password Reset Request')
        //             ->view('emails.password_reset')
        //             ->with([
        //                 'resetLink' => url('api/password/reset', $this->token)
        //             ]);
        $frontEndUrl = env('APP_FRONT_END_URL').'reset-password/reset-password.php?email='.$this->email.'&token='. $this->token;
        return $this->subject('Password Reset Request')
                    ->view('emails.password_reset')
                    ->with([
                        'resetLink' =>$frontEndUrl
                ]);
    }
}
