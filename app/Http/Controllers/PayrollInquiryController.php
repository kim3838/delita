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

            $payFrequencyTypes = $filters->pay_frequency_types;
            $recentCount = $request->validated()['recent_count'];

            $payrollService = App::make(PayrollServiceInterface::class, [Company::findOrFail($request->validated()['company_id'])]);

            $latestWithRecent = $payrollService->getLatestWithRecent($payFrequencyTypes, $recentCount);

            return ResponseJson::successfulResponse([
                'recent' => Fractal::collection($latestWithRecent['recent'], ListTransformer::class)['data'],
                'latest' => Fractal::collection($latestWithRecent['latest'], ListTransformer::class)['data'],
            ]);
        }

        abort(404);
    }
}
