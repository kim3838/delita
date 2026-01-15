<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\ApprovalSettingApproverRepository;
use App\Blueprint\Repositories\ApprovalSettingRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\ApprovalSetting\ListApprovalSettingRequest;
use App\Http\Requests\ApprovalSetting\UpdateApprovalSettingRequest;
use App\Transformers\ApprovalSetting\PatchableTransformer;

class ApprovalSettingController extends Controller
{
    public function __construct(
        protected readonly ApprovalSettingRepository $repository,
        protected readonly ApprovalSettingApproverRepository $approverRepository
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

    public function update(UpdateApprovalSettingRequest $request, $approvalSettingId)
    {
        if($request->expectsJson()){

            if(!empty($request->validated()['spliced_approver_sequence'])){
                $this->approverRepository->batchDelete($request->validated()['spliced_approver_sequence']);
            }

            $approvalSetting = $this->repository->update($approvalSettingId, $request->validated());

            $approvalSequences = collect($request->validated()['approver_sequence'])->filter(function ($approvalSequence){
                return isset($approvalSequence['id']) && $approvalSequence['id'] != null;
            })->map(function ($approvalSequence){
                return [
                    'id' => $approvalSequence['id'],
                    'approval_setting_id' => $approvalSequence['approval_setting_id'],
                    'order' => $approvalSequence['order'],
                    'approver_id' => $approvalSequence['approver_id'],
                ];
            });

            foreach($approvalSequences as $approvalSequence){
                $this->approverRepository->update($approvalSequence['id'], $approvalSequence);
            }

            $newApprovalSequences = collect($request->validated()['approver_sequence'])->filter(function ($approvalSequence){
                return !isset($approvalSequence['id']) || $approvalSequence['id'] == null;
            })->map(function ($approvalSequence){
                return [
                    'order' => $approvalSequence['order'],
                    'approver_id' => $approvalSequence['approver_id'],
                ];
            })->sortBy('order')->values();

            $approvalSetting->approvers()->createMany($newApprovalSequences->toArray());

            return ResponseJson::successfulResponse([
                'approval_setting' => Fractal::item($approvalSetting, PatchableTransformer::class),
            ]);
        }

        abort(404);
    }
}
