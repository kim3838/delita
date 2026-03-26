<?php

namespace App\Notifications;

use App\Facades\Fractal;
use App\Mail\PayrollGeneratedMail;
use App\Models\Payroll;
use App\Models\User;
use App\Transformers\Payroll\BasicTransformer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PayrollGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $backoff = 5;
    public int $timeout = 30;

    public object $payrollItem;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $user,
        protected Payroll $payroll
    ){
        $this->onQueue('notifications');

        $payrollItem = Fractal::item($payroll, BasicTransformer::class);

        $this->payrollItem = (object) $payrollItem;
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
    public function toMail(object $notifiable): PayrollGeneratedMail
    {
        return new PayrollGeneratedMail($this->user, $this->payrollItem)
            ->to($notifiable->email, $notifiable->name);
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
