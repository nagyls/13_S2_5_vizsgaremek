<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $backendBaseUrl = rtrim((string) env('BACKEND_URL', 'http://127.0.0.1:8000'), '/');
        $logoUrl = $backendBaseUrl . '/images/logo2.svg';

        return (new MailMessage)
            ->subject('Email cím megerősítése - EseményTér')
            ->view('emails.verify-email', [
                'notifiable' => $notifiable,
                'actionUrl' => $verificationUrl,
                'logoUrl' => $logoUrl,
            ]);
    }

    /**
     * Generate the verification URL for the user.
     */
    protected function verificationUrl($notifiable)
    {
        $relativePath = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(24),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            false
        );

        $backendBaseUrl = rtrim((string) env('BACKEND_URL', 'http://127.0.0.1:8000'), '/');

        return $backendBaseUrl . $relativePath;
    }
}
