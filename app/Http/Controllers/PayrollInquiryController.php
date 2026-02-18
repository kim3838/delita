<?php

namespace App\Http\Controllers;

use App\Blueprint\PayrollServiceInterface;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PayrollInquiry\PayrollInquiryRequest;
use App\Models\Company;
use App\Transformers\PayrollPayload\ListTransformer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;

class PayrollInquiryController extends Controller
{
    /**
     * @throws BindingResolutionException
     */
    public function index(PayrollInquiryRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $companyId = $request->validated()['company_id'];

            $payFrequencyTypes = $filters->pay_frequency_types;
            $recentCount = $request->validated()['recent_count'];

            $payrollService = App::make(PayrollServiceInterface::class, [Company::findOrFail($companyId)]);

            $currentWithRecent = $payrollService->getCurrentWithRecent($companyId, $payFrequencyTypes, $recentCount);

            $recentPayrolls = Fractal::collection($currentWithRecent['recent'], ListTransformer::class)['data'];
            $currentPayrolls = Fractal::collection($currentWithRecent['current'], ListTransformer::class)['data'];

            return ResponseJson::successfulResponse([
                'recent' => $recentPayrolls,
                'current' => $currentPayrolls,
            ]);
        }

        abort(404);
    }
}
