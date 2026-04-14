<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = trim((string) env('FRONTEND_URL', ''));

        if ($frontendUrl !== '') {
            $frontendBaseUrl = rtrim($frontendUrl, '/');
        } else {
            $frontendBaseUrl = rtrim((string) env('APP_URL', ''), '/');
        }

        $email = $notifiable->getEmailForPasswordReset();

        if ($frontendBaseUrl !== '') {
            $resetUrl = $frontendBaseUrl.'/reset-password?token='.urlencode($this->token).'&email='.urlencode($email);
        } else {
            $resetUrl = url('/reset-password?token='.urlencode($this->token).'&email='.urlencode($email));
        }

        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Jelszó-visszaállítás - EseményTér')
            ->view('emails.reset-password', [
                'notifiable' => $notifiable,
                'actionUrl' => $resetUrl,
                'expiryMinutes' => $expiryMinutes,
            ])
            ->withSymfonyMessage(function (Email $message) {
                $message->embedFromPath(
                    public_path('images/logo2.png'),
                    'logo',
                    'image/png'
                );
            });
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}