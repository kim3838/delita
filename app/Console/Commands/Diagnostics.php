<?php

namespace App\Console\Commands;

use App\Concrete\Tests\AttendancePayItemsTests;
use App\Concrete\Tests\AttendanceSplitterTests;
use App\Concrete\Tests\StandardPagIBIGContributionTests;
use App\Concrete\Tests\StandardPhilhealthContributionTests;
use App\Concrete\Tests\StandardSSSEmployedContributionTests;
use App\Concrete\Tests\StandardWithholdingTaxCompensationTests;
use App\Models\Company;
use Illuminate\Console\Command;

class Diagnostics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:diagnostics {company?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run test cases';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        _clear_debug();
        $company = Company::query()->find($this->argument('company'));

        $this->line('Diagnostics command executed');
        $this->newLine();
        $this->line('Running tests');

        if($company){
            $this->info('Attendance splitter: ' . new AttendanceSplitterTests($company)->run());
        }

        $this->info('Attendance pay items: ' . new AttendancePayItemsTests()->run(true, false));
        $this->info('Standard SSS employed: ' . new StandardSSSEmployedContributionTests()->run(true, false));
        $this->info('Standard Philhealth: ' . new StandardPhilhealthContributionTests()->run(true, false));
        $this->info('Standard Pag-IBIG: ' . new StandardPagIBIGContributionTests()->run(true, false));
        $this->info('Standard Withholding tax Compensation: ' . new StandardWithholdingTaxCompensationTests()->run(true, false));

        $this->newLine();
        $this->line('Done');
    }
}
