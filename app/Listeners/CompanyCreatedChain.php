<?php

namespace App\Listeners;

use App\Blueprint\Repositories\FormulaRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Concrete\ApprovalService;
use App\Enums\CompanyUserAssignmentType;
use App\Events\Repositories\CompanyCreated;
use App\Models\User;
use Illuminate\Support\Facades\App;

class CompanyCreatedChain
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompanyCreated $event): void
    {
        //Sync company assignment to the creator if not superadmin
        if($event->user instanceof User && !$event->user->isSuperAdmin()){

            $event->user->companies()->syncWithoutDetaching([
                $event->company->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]
            ]);
        }

        //Create pay frequency defaults
        foreach (App::make(PayFrequencyRepository::class)->defaultPresets() as $payFrequency) {

            $event->company->payFrequencies()->create($payFrequency);
        }

        /**
         * Sync basic formulas with default settings
         * Standard-Basic-Pay
         * Standard-Allowance
         * Standard-Overtime
         * Standard-Leave-Pay
         * Standard-Holiday-Pay
         * Standard-13th-Month
         * Standard-Taxable-Income
         * Standard-Nontaxable-Income
         * Standard-Compensation-Tax
         * Standard-Net-Income
         **/
        $formulas = [];

        foreach (App::make(FormulaRepository::class)->defaultPresets() as $formula) {

            $settings = empty($formula->default_settings?->cast) ? null : json_encode($formula->default_settings?->cast);

            $formulas[$formula->id] = ['settings' => $settings];
        }

        /**
         * Create salary statement modules
         **/
        foreach (App::make(SalaryStatementModuleRepository::class)->defaultPresets() as $salaryStatementModule) {

            $event->company->salaryStatementModules()->create($salaryStatementModule);
        }

        $event->company->formulas()->sync($formulas);

        /**
         * Create approval settings for requestable model
         **/
        foreach(ApprovalService::$seriesMap as $approvalSetting){
            $event->company->approvalSettings()->firstOrCreate(['request_model' => $approvalSetting['model_alias']]);
        }
    }
}
