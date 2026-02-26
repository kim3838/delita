<?php

namespace App\Listeners;

use App\Events\Repositories\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCreatedChain implements ShouldQueue
{
    public string $queue = 'listeners';

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
}
