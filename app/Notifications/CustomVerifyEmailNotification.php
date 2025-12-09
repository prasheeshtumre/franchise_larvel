<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // return (new MailMessage)
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', url('/'))
        //             ->line('Thank you for using our application!');

        return (new MailMessage)
                    ->subject('Please Verify Your Email Address') // Custom subject
                    ->view('emails.verify-email', [
                        'user' => $notifiable, // Passing the user model to the view
                        'verificationUrl' => $this->verificationUrl($notifiable) // Verification URL
                    ]);
    }

    protected function verificationUrl($notifiable)
    {
        // return url('api/email/verify/' . $notifiable->getKey() . '/' . sha1($notifiable->email));
        // return route('verification.verify', [
        //     'id' => $notifiable->getKey(),
        //     'hash' => sha1($notifiable->email),
        // ]);

        // Set the expiration time (for example, 60 minutes from now)
        $expires = Carbon::now()->addMinutes(60)->timestamp;
        $userId= $notifiable->getKey();
        $email= sha1($notifiable->email);
        // Step 2: Build the base URL and append the parameters
        $urlBase = env('APP_URL')."api/email/verify/$userId/$email";

        // Step 3: Add the expiration parameter
        $urlWithParams = $urlBase . "?expires={$expires}";

        // Step 4: Generate the signature using the base URL with parameters
        $signature = hash_hmac('sha256', $urlWithParams, Config::get('app.key'));

        // $signedUrl = $urlWithParams . "&signature={$signature}";
        // return  $signedUrl;
        // Generate the signature
        // $signature = hash_hmac('sha256', $url . $expires, config('app.key'));

        return env('APP_FRONT_END_URL').'verify/verify.php?id='.$userId.'&hash='.$email.'&expires='.$expires.'&signature='.$signature;
        // return URL::temporarySignedRoute(
        //     'verification.verify',
        //     now()->addMinutes(60), // URL expires in 60 minutes
        //     ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->email)]
        // );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
