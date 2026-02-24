<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Enums\RequestApprovalStatus;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\RequestApprovalState\ApplyWorkflowOnRequestApprovalStateRequest;
use App\Http\Requests\RequestApprovalState\ListRequestApprovalStateRequest;
use App\Transformers\RequestApprovalState\ListTransformer;

class RequestApprovalStateController extends Controller
{
    public function __construct(
        protected RequestApprovalStateRepository $repository,
    ){}

    public function index(ListRequestApprovalStateRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    /**
     * @throws UnexpectedException
     */
    public function applyWorkflow(ApplyWorkflowOnRequestApprovalStateRequest $request)
    {
        if($request->expectsJson()){

            $companyId = data_get($request->validated(), 'company_id');
            $accountId = data_get($request->validated(), 'account_id');
            $action = data_get($request->validated(), 'action');
            $remarks = data_get($request->validated(), 'remarks');
            $approvalStates = data_get($request->validated(), 'approval_states', []);

            return ResponseJson::successfulResponse([
                'results' => $this->repository->applyWorkflow($accountId, $companyId, RequestApprovalStatus::tryFrom($action), $remarks, $approvalStates)
            ]);
        }

        abort(404);
    }
}
