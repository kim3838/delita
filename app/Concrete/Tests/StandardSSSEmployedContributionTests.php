<?php

namespace App\Concrete\Tests;

use App\Actions\Formula\StandardSSSEmployedContributionFormula;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\JsonPresetRepository;
use Illuminate\Support\Facades\Storage;

class StandardSSSEmployedContributionTests
{
    public bool $testAll = false;

    public function testCases(): array
    {
        return [
            [
                'test_case' => '₱3630.0',
                'test' => false || $this->testAll,
                'monthly' => 24222,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 200.0, 'total' => 1200.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 400.0, 'ec' => 30.0, 'total' => 2430.0],
                    'total' => 3630.0
                ]
            ],
            [
                'test_case' => '₱760.0',
                'test' => false || $this->testAll,
                'monthly' => 5249.99,
                'expected' => [
                    'employee_share' => ['regular' => 250.0, 'mpf' => 0.0, 'total' => 250.0],
                    'employer_share' => ['regular' => 500.0, 'mpf' => 0.0, 'ec' => 10.0, 'total' => 510.0],
                    'total' => 760.0
                ]
            ],
            [
                'test_case' => '₱835.0',
                'test' => false || $this->testAll,
                'monthly' => 5250,
                'expected' => [
                    'employee_share' => ['regular' => 275.0, 'mpf' => 0.0, 'total' => 275.0],
                    'employer_share' => ['regular' => 550.0, 'mpf' => 0.0, 'ec' => 10.0, 'total' => 560.0],
                    'total' => 835.0
                ]
            ],
            [
                'test_case' => '₱1,285.0',
                'test' => false || $this->testAll,
                'monthly' => 8500,
                'expected' => [
                    'employee_share' => ['regular' => 425.0, 'mpf' => 0.0, 'total' => 425.0],
                    'employer_share' => ['regular' => 850.0, 'mpf' => 0.0, 'ec' => 10.0, 'total' => 860.0],
                    'total' => 1285.0
                ]
            ],
            [
                'test_case' => '₱2280.0',
                'test' => false || $this->testAll,
                'monthly' => 15000,
                'expected' => [
                    'employee_share' => ['regular' => 750.0, 'mpf' => 0.0, 'total' => 750.0],
                    'employer_share' => ['regular' => 1500.0, 'mpf' => 0.0, 'ec' => 30.0, 'total' => 1530.0],
                    'total' => 2280.0
                ]
            ],
            [
                'test_case' => '₱2,430.0',
                'test' => false || $this->testAll,
                'monthly' => 16000,
                'expected' => [
                    'employee_share' => ['regular' => 800.0, 'mpf' => 0.0, 'total' => 800.0],
                    'employer_share' => ['regular' => 1600.0, 'mpf' => 0.0, 'ec' => 30.0, 'total' => 1630.0],
                    'total' => 2430.0
                ]
            ],
            [
                'test_case' => '₱2,955.0',
                'test' => false || $this->testAll,
                'monthly' => 19500,
                'expected' => [
                    'employee_share' => ['regular' => 975.0, 'mpf' => 0.0, 'total' => 975.0],
                    'employer_share' => ['regular' => 1950.0, 'mpf' => 0.0, 'ec' => 30.0, 'total' => 1980.0],
                    'total' => 2955.0
                ]
            ],
            [
                'test_case' => '₱3,030.0',
                'test' => false || $this->testAll,
                'monthly' => 20249.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 0.0, 'total' => 1000.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 0.0, 'ec' => 30.0, 'total' => 2030.0],
                    'total' => 3030.0
                ]
            ],
            [
                'test_case' => '₱3,105.0',
                'test' => false || $this->testAll,
                'monthly' => 20749.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 25.0, 'total' => 1025.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 50.0, 'ec' => 30.0, 'total' => 2080.0],
                    'total' => 3105.0
                ]
            ],
            [
                'test_case' => '₱3,180.0',
                'test' => false || $this->testAll,
                'monthly' => 21249.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 50.0, 'total' => 1050.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 100.0, 'ec' => 30.0, 'total' => 2130.0],
                    'total' => 3180.0
                ]
            ],
            [
                'test_case' => '₱4,605.0',
                'test' => false || $this->testAll,
                'monthly' => 30749.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 525.0, 'total' => 1525.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 1050.0, 'ec' => 30.0, 'total' => 3080.0],
                    'total' => 4605.0
                ]
            ],
            [
                'test_case' => '₱5,205.0',
                'test' => false || $this->testAll,
                'monthly' => 34749.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 725.0, 'total' => 1725.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 1450.0, 'ec' => 30.0, 'total' => 3480.0],
                    'total' => 5205.0
                ]
            ],
            [
                'test_case' => '₱5,280.0',
                'test' => false || $this->testAll,
                'monthly' => 34750.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 750.0, 'total' => 1750.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 1500.0, 'ec' => 30.0, 'total' => 3530.0],
                    'total' => 5280.0
                ]
            ],
            [
                'test_case' => '₱5,280.0',
                'test' => false || $this->testAll,
                'monthly' => 35249.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 750.0, 'total' => 1750.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 1500.0, 'ec' => 30.0, 'total' => 3530.0],
                    'total' => 5280.0
                ]
            ],
            [
                'test_case' => '₱5,280.0',
                'test' => false || $this->testAll,
                'monthly' => 35749.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 750.0, 'total' => 1750.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 1500.0, 'ec' => 30.0, 'total' => 3530.0],
                    'total' => 5280.0
                ]
            ],
            [
                'test_case' => '₱5,280.0',
                'test' => false || $this->testAll,
                'monthly' => 90749.99,
                'expected' => [
                    'employee_share' => ['regular' => 1000.0, 'mpf' => 750.0, 'total' => 1750.0],
                    'employer_share' => ['regular' => 2000.0, 'mpf' => 1500.0, 'ec' => 30.0, 'total' => 3530.0],
                    'total' => 5280.0
                ]
            ],
        ];
    }

    public function run($all = false, $debug = false): string
    {
        $this->testAll = $all;
        $passed = 0;
        $failed = 0;

        $standardSSSEmployedJsonPreset = app(JsonPresetRepository::class)->model()::query()
            ->where('key', 'standard_sss_employed_contribution')->first();

        if(empty($standardSSSEmployedJsonPreset)) return 'No preset found';

        $jsonContent = Storage::disk($standardSSSEmployedJsonPreset->disk)->get($standardSSSEmployedJsonPreset->path);

        $companyFormulaRepository = app(CompanyFormulaRepository::class);
        $formulableModelHydrated = $companyFormulaRepository->hydrateItem([
            'settings' => $jsonContent
        ]);

        $castedSettingsFromFormulableModel = $formulableModelHydrated->settings->cast;

        $formula = app(StandardSSSEmployedContributionFormula::class);

        foreach ($this->testCases() as $testCase){

            if(!$testCase['test'])continue;

            $result = $formula->getContribution($castedSettingsFromFormulableModel, $testCase['monthly']);

            if(
                $testCase['expected'] == $result
            ){
                $passed++;
            } else {
                $failed++;

                _debug([
                    'Test case failed' => $testCase['test_case'],
                ]);
            }
        }

        return sprintf('%d/%d', $passed, $passed + $failed);
    }
}
