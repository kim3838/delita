<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;

class NewUserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $backoff = 5;
    public int $timeout = 30;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected $username,
        protected $email,
        protected $password
    ){
        $this->onQueue('notifications');
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
        return (new MailMessage)
            ->subject('User Account Credentials')
            ->view(
                [
                    'email.html.user.registered',
                    'email.text.user.registered'
                ], [
                    'username' => $this->username,
                    'email' => $this->email,
                    'password' => $this->password
                ]
            );
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

    public function middleware(object $notifiable, string $channel): array
    {
        return match ($channel) {
            'mail' => [
                new RateLimited('new_user_credentials_notification')
            ],
            default => [],
        };
    }
}
