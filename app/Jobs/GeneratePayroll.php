<?php

namespace App\Jobs;

use App\Blueprint\PayrollServiceInterface;
use App\Exceptions\UnexpectedException;
use App\Models\Company;
use App\Models\Payroll;
use App\Models\User;
use App\Notifications\PayrollGeneratedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeneratePayroll implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Company $company,
        public User $user,
        public Payroll $payroll,
        public array $employeeIds
    ){
        $this->onQueue('payroll');
    }

    /**
     * Execute the job.
     * @throws UnexpectedException
     */
    public function handle(): void
    {
        $payrollService = app(PayrollServiceInterface::class, [$this->company]);

        $payrollService->generateSalaryStatements($this->payroll, $this->employeeIds);

        $this->user->notify(new PayrollGeneratedNotification($this->user, $this->payroll));
    }
}
