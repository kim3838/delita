<?php

namespace App\Http\Controllers\Internal;

use App\Actions\Auth\Throttle;
use App\Actions\Formula\StandardPagIBIGContributionFormula;
use App\Actions\Formula\StandardPhilhealthContributionFormula;
use App\Actions\Formula\StandardSSSEmployedContributionFormula;
use App\Actions\Formula\StandardWithHoldingTaxCompensationFormula;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\JsonPresetRepository;
use App\Enums\PayFrequency;
use App\Facades\MoneyFormat;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaxCalculator\TaxCalculatorRequest;
use App\Models\JsonPreset;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class TaxCalculatorController extends Controller
{

    public function store(TaxCalculatorRequest $request)
    {
        if($request->expectsJson()){

            if(RateLimiter::tooManyAttempts(Throttle::key($request), 3)){

                event(new Lockout($request));

                $availableIn = RateLimiter::availableIn(Throttle::key($request));

                $rateLimitExceedsMessage = "Too many attempts, try again in $availableIn second" . ($availableIn > 0 ? 's' : '') . ".";

                return ResponseJson::tooManyRequestsResponse($rateLimitExceedsMessage);
            }

            RateLimiter::hit(Throttle::key($request));

            $validated = $request->validated();
            $monthlySalary = $validated['monthly_salary'];

            $monthlySalary = BigDecimal::of((string)$monthlySalary);
            $sssContribution = BigDecimal::zero();
            $philHealthContribution = BigDecimal::zero();
            $pagIbigContribution = BigDecimal::zero();
            $taxable = BigDecimal::zero();
            $net = BigDecimal::zero();

            $standardSSSEmployedJsonPreset = app(JsonPresetRepository::class)->model()::query()
                ->where('key', 'standard_sss_employed_contribution')->first();
            if(empty($standardSSSEmployedJsonPreset)) return ResponseJson::notFoundResponse('Formula preset not found');
            $sssFormulaSettings = $this->getCastedSettingsFromFormulableModel($standardSSSEmployedJsonPreset);
            $sssFormula = app(StandardSSSEmployedContributionFormula::class);
            $sssContributionResult = $sssFormula->getContribution($sssFormulaSettings, $monthlySalary);
            $sssContribution = $sssContribution->plus(BigDecimal::of($sssContributionResult['employee_share']['total']));

            $standardPhilhealthJsonPreset = app(JsonPresetRepository::class)->model()::query()
                ->where('key', 'standard_philhealth_contribution')->first();
            if(empty($standardPhilhealthJsonPreset)) return ResponseJson::notFoundResponse('Formula preset not found');
            $philhealthFormulaSettings = $this->getCastedSettingsFromFormulableModel($standardPhilhealthJsonPreset);
            $philhealthFormula = app(StandardPhilhealthContributionFormula::class);
            $philhealthContributionResult = $philhealthFormula->getContribution($philhealthFormulaSettings, $monthlySalary);
            $philHealthContribution = $philHealthContribution->plus(BigDecimal::of($philhealthContributionResult['employee_share']['total']));

            $standardPagIBIGJsonPreset = app(JsonPresetRepository::class)->model()::query()
                ->where('key', 'standard_pag_ibig_contribution')->first();
            if(empty($standardPagIBIGJsonPreset)) return ResponseJson::notFoundResponse('Formula preset not found');
            $pagIbigFormulaSettings = $this->getCastedSettingsFromFormulableModel($standardPagIBIGJsonPreset);
            $pagIbigFormula = app(StandardPagIBIGContributionFormula::class);
            $pagIbigContributionResult = $pagIbigFormula->getContribution($pagIbigFormulaSettings, $monthlySalary);
            $pagIbigContribution = $pagIbigContribution->plus(BigDecimal::of($pagIbigContributionResult['employee_share']['total']));

            $taxable = $monthlySalary->minus($sssContribution)->minus($philHealthContribution)->minus($pagIbigContribution);

            $standardWithholdingTaxCompensationJsonPreset = app(JsonPresetRepository::class)->model()::query()
                ->where('key', 'standard_withholding_tax_compensation')->first();
            if(empty($standardWithholdingTaxCompensationJsonPreset)) return ResponseJson::notFoundResponse('Formula preset not found');
            $withholdingTaxCompensationFormulaSettings = $this->getCastedSettingsFromFormulableModel($standardWithholdingTaxCompensationJsonPreset);
            $withholdingTaxCompensationFormula = app(StandardWithholdingTaxCompensationFormula::class);
            $withholdingTaxCompensation = $withholdingTaxCompensationFormula->getIntended($withholdingTaxCompensationFormulaSettings, $taxable, PayFrequency::MONTHLY);
            $withholdingTaxCompensation = BigDecimal::of($withholdingTaxCompensation);

            $net = $taxable->minus($withholdingTaxCompensation);

            return ResponseJson::successfulResponse([
                'result' => [
                    'contributions' => [
                        'sss' => MoneyFormat::toLocale($sssContribution, 'PHP'),
                        'philhealth' => MoneyFormat::toLocale($philHealthContribution, 'PHP'),
                        'pag_ibig' => MoneyFormat::toLocale($pagIbigContribution, 'PHP'),
                    ],
                    'taxable' => [
                        'taxable' => MoneyFormat::toLocale($taxable, 'PHP'),
                        'withholding_tax' => MoneyFormat::toLocale($withholdingTaxCompensation, 'PHP'),
                        'net' => MoneyFormat::toLocale($net, 'PHP'),
                    ]
                ]
            ]);
        }

        abort(404);
    }

    public function getCastedSettingsFromFormulableModel(JsonPreset $jsonPreset)
    {
        $jsonContent = Storage::disk($jsonPreset->disk)->get($jsonPreset->path);

        $companyFormulaRepository = app(CompanyFormulaRepository::class);
        $formulableModelHydrated = $companyFormulaRepository->hydrateItem([
            'settings' => $jsonContent
        ]);

        return $formulableModelHydrated->settings->cast;
    }
}
