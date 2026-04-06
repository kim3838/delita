<?php

namespace App\Notifications;

use App\Concrete\AwaitingApprovalContext;
use App\Mail\AttendanceAdjustmentRequestMail;
use App\Mail\LeaveRequestMail;
use App\Mail\OvertimeRequestMail;
use App\Mail\PayrollRequestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class AwaitingApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $backoff = 5;
    public int $timeout = 30;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AwaitingApprovalContext $context
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
    public function toMail(object $notifiable): Mailable
    {
        $mail = match($this->context->requestableType){
            'attendance_adjustment_request' => new AttendanceAdjustmentRequestMail($this->context->approver, $this->context),
            'overtime_request' => new OvertimeRequestMail($this->context->approver, $this->context),
            'leave_request' => new LeaveRequestMail($this->context->approver, $this->context),
            'payroll_request' => new PayrollRequestMail($this->context->approver, $this->context),
        };

        return $mail->to($notifiable->email, $notifiable->name);
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
