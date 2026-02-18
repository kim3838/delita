<?php

namespace App\Concrete\Tests;

use App\Actions\Formula\StandardWithHoldingTaxCompensationFormula;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\JsonPresetRepository;
use App\Enums\PayFrequency;
use Illuminate\Support\Facades\Storage;

class StandardWithholdingTaxCompensationTests
{
    public bool $testAll = false;

    public function testCases(): array
    {
        return [
            [
                'test_case' => '₱0.0',
                'test' => false || $this->testAll,
                'compensation' => 1500,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '0.000000'
            ],
            [
                'test_case' => '₱0.0',
                'test' => false || $this->testAll,
                'compensation' => 20833,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '0.000000'
            ],
            [
                'test_case' => '₱0.15',
                'test' => false || $this->testAll,
                'compensation' => 20834,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '0.150000'
            ],
            [
                'test_case' => '₱1,375.05',
                'test' => false || $this->testAll,
                'compensation' => 30000,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '1375.050000'
            ],
            [
                'test_case' => '₱1,875.00',
                'test' => false || $this->testAll,
                'compensation' => 33333,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '1875.000000'
            ],
            [
                'test_case' => '₱5,904.72',
                'test' => false || $this->testAll,
                'compensation' => 53481.60,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '5904.720000'
            ],
            [
                'test_case' => '₱6,518.42',
                'test' => false || $this->testAll,
                'compensation' => 56550.00,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '6518.400000'
            ],
            [
                'test_case' => '₱7,208.40',
                'test' => false || $this->testAll,
                'compensation' => 60000,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '7208.400000'
            ],
            [
                'test_case' => '₱183,541.80',
                'test' => false || $this->testAll,
                'compensation' => 666667,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '183541.800000'
            ],
            [
                'test_case' => '₱183,542.1465',
                'test' => false || $this->testAll,
                'compensation' => 666667.99,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '183542.146500'
            ],
            [
                'test_case' => '₱195,208.35',
                'test' => false || $this->testAll,
                'compensation' => 700000.00,
                'frequency' => PayFrequency::MONTHLY,
                'expected' => '195208.350000'
            ],
        ];
    }

    public function run($all = false, $debug = false): string
    {
        $this->testAll = $all;
        $passed = 0;
        $failed = 0;

        $standardWithholdingTaxCompensationJsonPreset = app(JsonPresetRepository::class)->model()::query()
            ->where('key', 'standard_withholding_tax_compensation')->first();

        if(empty($standardWithholdingTaxCompensationJsonPreset)) return 'No preset found';

        $jsonContent = Storage::disk($standardWithholdingTaxCompensationJsonPreset->disk)->get($standardWithholdingTaxCompensationJsonPreset->path);

        $companyFormulaRepository = app(CompanyFormulaRepository::class);
        $formulableModelHydrated = $companyFormulaRepository->hydrateItem([
            'settings' => $jsonContent
        ]);

        $castedSettingsFromFormulableModel = $formulableModelHydrated->settings->cast;

        $formula = app(StandardWithHoldingTaxCompensationFormula::class);

        foreach ($this->testCases() as $testCase){

            if(!$testCase['test'])continue;

            $result = $formula->getIntended($castedSettingsFromFormulableModel, $testCase['compensation'], $testCase['frequency']);

            if(
                $testCase['expected'] == $result
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
