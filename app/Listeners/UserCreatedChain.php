<?php

namespace App\Listeners;

use App\Events\Repositories\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;

class UserCreatedChain implements ShouldQueue
{
    public string $queue = 'listeners';
    public int $tries = 5;
    public int $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        //Mail user account verification email
        $emailForVerification = $event->user->getEmailForVerification();

        if(!empty($emailForVerification)) $event->user->sendEmailVerificationNotification();
    }

    public function middleware(): array
    {
        return [
            new RateLimited('new_user_verification_notification')
        ];
    }
}
