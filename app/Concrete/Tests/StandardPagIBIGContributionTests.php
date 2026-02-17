<?php

namespace App\Concrete\Tests;

use App\Actions\Formula\StandardPagIBIGContributionFormula;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\JsonPresetRepository;
use Illuminate\Support\Facades\Storage;

class StandardPagIBIGContributionTests
{
    public bool $testAll = false;

    public function testCases(): array
    {
        return [
            [
                'test_case' => '₱45.0',
                'test' => false || $this->testAll,
                'compensation' => 1500,
                'expected' => [
                    'employee_share' => '15.000000',
                    'employer_share' => '30.000000',
                    'total' => '45.000000'
                ]
            ],
            [
                'test_case' => '₱100.0',
                'test' => false || $this->testAll,
                'compensation' => 2500,
                'expected' => [
                    'employee_share' => '50.000000',
                    'employer_share' => '50.000000',
                    'total' => '100.000000'
                ]
            ],
            [
                'test_case' => '₱399.99960',
                'test' => false || $this->testAll,
                'compensation' => 9999.99,
                'expected' => [
                    'employee_share' => '199.999800',
                    'employer_share' => '199.999800',
                    'total' => '399.999600'
                ]
            ],
            [
                'test_case' => '₱400.0',
                'test' => false || $this->testAll,
                'compensation' => 10000.00,
                'expected' => [
                    'employee_share' => '200.000000',
                    'employer_share' => '200.000000',
                    'total' => '400.000000'
                ]
            ],
            [
                'test_case' => '₱400.0',
                'test' => false || $this->testAll,
                'compensation' => 11500.00,
                'expected' => [
                    'employee_share' => '200.000000',
                    'employer_share' => '200.000000',
                    'total' => '400.000000'
                ]
            ],
            [
                'test_case' => '₱400.0',
                'test' => false || $this->testAll,
                'compensation' => 21500.00,
                'expected' => [
                    'employee_share' => '200.000000',
                    'employer_share' => '200.000000',
                    'total' => '400.000000'
                ]
            ],
        ];
    }

    public function run($all = false, $debug = false): string
    {
        $this->testAll = $all;
        $passed = 0;
        $failed = 0;

        $standardPagIBIGJsonPreset = app(JsonPresetRepository::class)->model()::query()
            ->where('key', 'standard_pag_ibig_contribution')->first();

        if(empty($standardPagIBIGJsonPreset)) return 'No preset found';

        $jsonContent = Storage::disk($standardPagIBIGJsonPreset->disk)->get($standardPagIBIGJsonPreset->path);

        $companyFormulaRepository = app(CompanyFormulaRepository::class);
        $formulableModelHydrated = $companyFormulaRepository->hydrateItem([
            'settings' => $jsonContent
        ]);

        $castedSettingsFromFormulableModel = $formulableModelHydrated->settings->cast;

        $formula = app(StandardPagIBIGContributionFormula::class);

        foreach ($this->testCases() as $testCase){

            if(!$testCase['test'])continue;

            $result = $formula->getContribution($castedSettingsFromFormulableModel, $testCase['compensation']);

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
