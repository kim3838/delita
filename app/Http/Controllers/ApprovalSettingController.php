<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\ApprovalSettingRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\ApprovalSetting\ListApprovalSettingRequest;
use App\Transformers\ApprovalSetting\PatchableTransformer;

class ApprovalSettingController extends Controller
{
    public function __construct(
        protected readonly ApprovalSettingRepository $repository
    ){}

    public function indexGate(ListApprovalSettingRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function index(ListApprovalSettingRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->list($filters),
                PatchableTransformer::class,
                'approval_settings'
            ));
        }

        abort(404);
    }
}
