<?php

namespace App\Jobs;

use App\Blueprint\PayrollServiceInterface;
use App\Enums\PayrollStatus;
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

    public int $timeout = 1200;

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
     *
     * @throws UnexpectedException
     */
    public function handle(): void
    {
        $payrollService = app(PayrollServiceInterface::class, [$this->company]);

        $payrollService->preProcessSalaryStatements($this->payroll);

        $chunkedEmployeeIds = array_chunk($this->employeeIds, 100);

        foreach($chunkedEmployeeIds as $employeeIdChunk){

            $payrollService->generateSalaryStatements($this->payroll, $employeeIdChunk);
        }

        $payrollService->postProcessSalaryStatements($this->payroll);

        $this->payroll->update([
            'status' => PayrollStatus::DRAFT
        ]);

        $this->user->notify(new PayrollGeneratedNotification($this->user, $this->payroll));
    }
}
