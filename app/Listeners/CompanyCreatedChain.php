<?php

namespace App\Listeners;

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

        //Sync standard formulas

    }
}
