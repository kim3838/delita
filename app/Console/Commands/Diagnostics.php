<?php

namespace App\Console\Commands;

use App\Concrete\Tests\AttendanceSplitterTests;
use App\Models\Company;
use Illuminate\Console\Command;

class Diagnostics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:diagnostics {company}';

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
        $company = Company::query()->findOrFail($this->argument('company'));

        $this->line('Diagnostics command executed');
        $this->newLine();
        $this->line('Running tests');

        $this->info('Attendance splitter: ' . new AttendanceSplitterTests($company)->run());

        $this->newLine();
        $this->line('Done');
    }
}
