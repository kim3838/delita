<?php

namespace App\Listeners;

use App\Blueprint\Repositories\FormulaRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Enums\CompanyUserAssignmentType;
use App\Events\Repositories\CompanyCreated;
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
        if(!$event->user->isSuperAdmin()){

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
         * Standard-Basic-Salary
         * Standard-Overtime
         * Standard-Taxable-Income
         * Standard-Nontaxable-Income
         * Standard-Compensation-Tax
         * Standard-Net-Income
         **/
        $sync = [];

        foreach (App::make(FormulaRepository::class)->defaultPresets() as $formula) {

            $settings = empty($formula->default_settings?->cast) ? null : json_encode($formula->default_settings?->cast);

            $sync[$formula->id] = ['settings' => $settings];
        }

        $event->company->formulas()->sync($sync);
    }
}
