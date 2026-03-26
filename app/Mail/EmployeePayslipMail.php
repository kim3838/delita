<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeePayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payslip of $this->payrollMonthReadable",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'notification.employee-payslip-generated',
            with: [
                'payroll_number' => $this->payrollNumber,
                'payroll_month_readable' => $this->payrollMonthReadable,
                'payroll_frequency' => $this->payrollFrequency,
                'employee_number' => $this->employeeNumber,
                'employee_full_name' => $this->employeeFullName,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorage($this->payslipFilePath)
                ->as($this->payslipFilename)
                ->withMime('application/pdf'),
        ];
    }
}
