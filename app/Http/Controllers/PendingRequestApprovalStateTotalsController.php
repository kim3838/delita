<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\RequestApprovalState\TotalsTransformer;
use Illuminate\Http\Request;

class PendingRequestApprovalStateTotalsController extends Controller
{
    public function __construct(
        protected RequestApprovalStateRepository $repository,
    ){}

    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $totals = $this->repository->totals($filters);

            $totals = Fractal::item($totals->first(), TotalsTransformer::class);

            return ResponseJson::successfulResponse([
                'totals' => $totals
            ]);
        }

        abort(404);
    }
}
