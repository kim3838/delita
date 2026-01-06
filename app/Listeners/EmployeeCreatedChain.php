<?php

namespace App\Listeners;

use App\Enums\CreationType;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Events\Repositories\EmployeeCreated;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EmployeeCreatedChain
{
    public bool $createInitialEmploymentProfile = false;
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
    public function handle(EmployeeCreated $event): void
    {
        //If an employee is imported, create a default employment profile
        if($this->createInitialEmploymentProfile && $event->employee->creation_type == CreationType::IMPORT){

            $event->employee->employmentProfiles()->create([
                'status' => EmploymentStatus::ACTIVE,
                'employment_type' => EmploymentType::NOT_SPECIFIED,
                'start_date' => Carbon::now()->toDateString()
            ]);
        }
    }
}
