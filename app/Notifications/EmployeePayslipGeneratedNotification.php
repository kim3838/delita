<?php

namespace App\Notifications;

use App\Mail\EmployeePayslipMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeePayslipGeneratedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public object $proxyNotifiable,
        public string $payrollNumber,
        public string $payrollMonthReadable,
        public string $payrollFrequency,
        public string $employeeNumber,
        public string $employeeFullName,
        public string $payslipFilename,
        public string $payslipFilePath,
    ){}

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
    public function toMail(object $notifiable): EmployeePayslipMail
    {
        $debugEnabled = false;

        if($debugEnabled){

            _debug([
                'To mail' => [
                    'Proxy notifiable' => $this->proxyNotifiable,
                    'Payroll number' => $this->payrollNumber,
                    'Payroll month readable' => $this->payrollMonthReadable,
                    'Payroll frequency' => $this->payrollFrequency,
                    'Employee number' => $this->employeeNumber,
                    'Employee full name' => $this->employeeFullName,
                    'Payslip filename' => $this->payslipFilename,
                    'Payslip file path' => $this->payslipFilePath,
                    'Notifiable' => $notifiable,
                ]
            ]);
        }

        return new EmployeePayslipMail(
            $this->proxyNotifiable,
            $this->payrollNumber,
            $this->payrollMonthReadable,
            $this->payrollFrequency,
            $this->employeeNumber,
            $this->employeeFullName,
            $this->payslipFilename,
            $this->payslipFilePath,
        )->to(
            $this->proxyNotifiable->email,
            $this->proxyNotifiable->name
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
}
