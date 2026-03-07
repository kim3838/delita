<?php

namespace Database\Seeders;

use App\Concrete\ApprovalService;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApprovalSettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(ApprovalService::$seriesMap as $approvalSetting){

            foreach(Company::all() as $company){

                $company->approvalSettings()->firstOrCreate([
                    'request_model' => $approvalSetting['model_alias'],
                    'employable' => $approvalSetting['employable'],
                ]);
            }
        }
    }
}
