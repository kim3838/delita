<?php

namespace App\Concrete\Tests;

use App\Actions\Formula\StandardPhilhealthContributionFormula;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\JsonPresetRepository;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Storage;

class StandardPhilhealthContributionTests
{
    public bool $testAll = false;

    public function testCases(): array
    {
        return [
            [
                'test_case' => '₱500.0',
                'test' => false || $this->testAll,
                'compensation' => 9222,
                'expected' => [
                    'employee_share' => [
                        'regular' => '250.000000',
                        'total' => '250.000000'
                    ],
                    'employer_share' => [
                        'regular' => '250.000000',
                        'total' => '250.000000'
                    ],
                    'total' => '500.000000'
                ]
            ],
            [
                'test_case' => '₱811.1',
                'test' => false || $this->testAll,
                'compensation' => 16222,
                'expected' => [
                    'employee_share' => [
                        'regular' => '405.550000',
                        'total' => '405.550000'
                    ],
                    'employer_share' => [
                        'regular' => '405.550000',
                        'total' => '405.550000'
                    ],
                    'total' => '811.100000'
                ]
            ],
            [
                'test_case' => '₱1,211.1',
                'test' => false || $this->testAll,
                'compensation' => 24222,
                'expected' => [
                    'employee_share' => [
                        'regular' => '605.550000',
                        'total' => '605.550000'
                    ],
                    'employer_share' => [
                        'regular' => '605.550000',
                        'total' => '605.550000'
                    ],
                    'total' => '1211.100000'
                ]
            ],
            [
                'test_case' => '₱1,809.385',
                'test' => false || $this->testAll,
                'compensation' => 36187.7,
                'expected' => [
                    'employee_share' => [
                        'regular' => '904.692500',
                        'total' => '904.692500'
                    ],
                    'employer_share' => [
                        'regular' => '904.692500',
                        'total' => '904.692500'
                    ],
                    'total' => '1809.385000'
                ]
            ],
            [
                'test_case' => '₱1,711.1',
                'test' => false || $this->testAll,
                'compensation' => 34222,
                'expected' => [
                    'employee_share' => [
                        'regular' => '855.550000',
                        'total' => '855.550000'
                    ],
                    'employer_share' => [
                        'regular' => '855.550000',
                        'total' => '855.550000'
                    ],
                    'total' => '1711.100000'
                ]
            ],
            [
                'test_case' => '₱3,000.0',
                'test' => false || $this->testAll,
                'compensation' => 60000,
                'expected' => [
                    'employee_share' => [
                        'regular' => '1500.000000',
                        'total' => '1500.000000'
                    ],
                    'employer_share' => [
                        'regular' => '1500.000000',
                        'total' => '1500.000000'
                    ],
                    'total' => '3000.000000'
                ]
            ],
            [
                'test_case' => '₱4,711.1',
                'test' => false || $this->testAll,
                'compensation' => 94222,
                'expected' => [
                    'employee_share' => [
                        'regular' => '2355.550000',
                        'total' => '2355.550000'
                    ],
                    'employer_share' => [
                        'regular' => '2355.550000',
                        'total' => '2355.550000'
                    ],
                    'total' => '4711.100000'
                ]
            ],
            [
                'test_case' => '₱5,000.0',
                'test' => false || $this->testAll,
                'compensation' => 100000,
                'expected' => [
                    'employee_share' => [
                        'regular' => '2500.000000',
                        'total' => '2500.000000'
                    ],
                    'employer_share' => [
                        'regular' => '2500.000000',
                        'total' => '2500.000000'
                    ],
                    'total' => '5000.000000'
                ]
            ],
            [
                'test_case' => '₱5,000.0',
                'test' => false || $this->testAll,
                'compensation' => 109222,
                'expected' => [
                    'employee_share' => [
                        'regular' => '2500.000000',
                        'total' => '2500.000000'
                    ],
                    'employer_share' => [
                        'regular' => '2500.000000',
                        'total' => '2500.000000'
                    ],
                    'total' => '5000.000000'
                ]
            ],
        ];
    }

    public function run($all = false, $debug = false): string
    {
        $this->testAll = $all;
        $passed = 0;
        $failed = 0;

        $standardPhilhealthJsonPreset = app(JsonPresetRepository::class)->model()::query()
            ->where('key', 'standard_philhealth_contribution')->first();

        if(empty($standardPhilhealthJsonPreset)) return 'No preset found';

        $jsonContent = Storage::disk($standardPhilhealthJsonPreset->disk)->get($standardPhilhealthJsonPreset->path);

        $companyFormulaRepository = app(CompanyFormulaRepository::class);
        $formulableModelHydrated = $companyFormulaRepository->hydrateItem([
            'settings' => $jsonContent
        ]);

        $castedSettingsFromFormulableModel = $formulableModelHydrated->settings->cast;

        $formula = app(StandardPhilhealthContributionFormula::class);

        foreach ($this->testCases() as $testCase){

            if(!$testCase['test'])continue;

            $result = $formula->getContribution($castedSettingsFromFormulableModel, $testCase['compensation']);

            $testCaseExpected = [
                'employee_share' =>
                    [
                        'regular' => BigDecimal::of($testCase['expected']['employee_share']['regular'])->toScale(4, RoundingMode::HalfUp)->toString(),
                        'total' => BigDecimal::of($testCase['expected']['employee_share']['total'])->toScale(4, RoundingMode::HalfUp)->toString()
                    ],
                'employer_share' =>
                    [
                        'regular' => BigDecimal::of($testCase['expected']['employer_share']['regular'])->toScale(4, RoundingMode::HalfUp)->toString(),
                        'total' => BigDecimal::of($testCase['expected']['employer_share']['total'])->toScale(4, RoundingMode::HalfUp)->toString()
                    ],
                'total' => BigDecimal::of($testCase['expected']['total'])->toScale(4, RoundingMode::HalfUp)->toString()
            ];

            if(
                $testCaseExpected == $result
            ){
                $passed++;
            } else {
                $failed++;

                _debug([
                    'Test case failed' => $testCase['test_case'],
                    'Expected' => $testCase['expected'],
                    'Result' => $result,
                ]);
            }
        }

        return sprintf('%d/%d', $passed, $passed + $failed);
    }
}
