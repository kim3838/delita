<?php

namespace App\Notifications;

use App\Concrete\LogContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;

class ErrorLogNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $backoff = 30;
    public int $timeout = 30;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public LogContext $logContext
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
        $status = $this->logContext->isError ? 'Error' : 'Exception';

        return (new MailMessage)
            ->error()
            ->subject("Application {$status}: " . $this->logContext->thrown)
            ->greeting("A new {$status} has been logged.")
            ->line("Message: " . $this->logContext->message)
            ->line("Location: {$this->logContext->file} on line {$this->logContext->line}")
            ->line("Request: " . $this->logContext->request);
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

    public function middleware(): array
    {
        return [
            new RateLimited('error_log_notification')
        ];
    }
}


