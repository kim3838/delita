<?php

namespace App\Jobs;

use App\Blueprint\PayslipServiceInterface;
use App\Models\SalaryStatement;
use App\Notifications\EmployeePayslipGeneratedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Notification;

class MailEmployeePayslip implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $backoff = 30;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SalaryStatement $salaryStatement
    ){
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $debugEnabled = false;

        if($debugEnabled){

            _debug([
                'Mail employee payslip' => [
                    'Salary statement id' => $this->salaryStatement->id,
                    'Payroll id' => $this->salaryStatement->payroll->id,
                    'Company id' => $this->salaryStatement->payroll->company->id,
                ]
            ]);
        }

        $payslipService = app(PayslipServiceInterface::class, [$this->salaryStatement->payroll->company]);

        $payslipService->generate($this->salaryStatement);

        $this->salaryStatement->update([
            'payslip_disk' => config('filesystems.default'),
            'payslip_path' => $payslipService->filePath(),
        ]);

        $params = $payslipService->params;

        $officeEmail = $this->salaryStatement->employee->contact->office_email;

        if(!empty($officeEmail)){

            if($debugEnabled){

                _debug([
                    'Default storage disk' => config('filesystems.default'),
                    'Office email' => $officeEmail,

                    'payroll_number' => $params['payroll_number'],
                    'payroll_month_readable' => $params['payroll_month_readable'],
                    'payroll_frequency' => $params['payroll_frequency'],
                    'employee_number' => $params['employee_number'],
                    'employee_full_name' => $params['employee_full_name'],
                    'payslip filename' => $payslipService->filename,
                    'payslip path' => $payslipService->filePath(),
                ]);
            }

            $proxyNotifiable = (object)[
                'email' => $officeEmail,
                'name' => $params['employee_full_name']
            ];

            Notification::route('mail', [
                $proxyNotifiable->email => $proxyNotifiable->name
            ])->notifyNow(new EmployeePayslipGeneratedNotification(
                $proxyNotifiable,
                $params['payroll_number'],
                $params['payroll_month_readable'],
                $params['payroll_frequency'],
                $params['employee_number'],
                $params['employee_full_name'],
                $payslipService->filename,
                $payslipService->filePath()
            ));
        }
    }

    public function middleware(): array
    {
        return [
            new RateLimited('cloudflare_browser_rendering')
        ];
    }
}
