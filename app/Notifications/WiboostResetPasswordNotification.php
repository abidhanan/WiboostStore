<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class WiboostResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $path = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        $resetUrl = rtrim((string) config('wiboost.public_url', config('app.url')), '/') . $path;

        return (new MailMessage)
            ->subject('Reset Password Wiboost Store')
            ->view('emails.password_reset', [
                'user' => $notifiable,
                'resetUrl' => $resetUrl,
                'expiresIn' => config('auth.passwords.users.expire', 60),
            ]);
    }
}
