<?php

namespace App\Traits;

use App\Concrete\ApprovalService;
use App\Enums\ApproverType;
use App\Enums\DepartmentEmployeeAssignmentType;
use App\Models\Company;
use Illuminate\Support\Collection;

trait HasApproval
{
    public function companyApprovalSettings(int|Company $company)
    {
        $company = $company instanceof Company ? $company : Company::query()->findOrFail($company);

        return $company->approvalSettings;
    }

    public function getRequestableRawApprovers($modelAlias, Collection $approvalSettings)
    {
        return $approvalSettings
            ->where('request_model', $modelAlias)
            ->first()?->approvers
            ->map(function($approver){
                return [
                    'order' => $approver->order,
                    'type' => $approver->type,
                    'approver_id' => $approver->approver_id
                ];
            })
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    public function getForeignPath($modelAlias)
    {
        $modelApprovalMap = collect(ApprovalService::$seriesMap)->where('model_alias', $modelAlias);

        return $modelApprovalMap->first()[$modelApprovalMap->first()['foreign_path']];
    }

    public function chainForeignPath($modelThroughForeign, $requestableModelAlias)
    {
        $finalModel = null;

        $requestableModelForeignPath = $this->getForeignPath($requestableModelAlias);

        if(!empty($requestableModelForeignPath)){

            foreach($requestableModelForeignPath as $path){

                $foreign = $path['foreign'];
                $foreignValue = empty($finalModel)
                    ? $modelThroughForeign
                    : $finalModel->$foreign;

                $finalModel = app($path['model'])
                    ->model()::query()->find($foreignValue);

                if(empty($finalModel)){break;}
            }
        }

        return $finalModel;
    }

    public function getRequestableApprovers($modelAlias, $modelThroughForeign, int|Company $company, $requesterId = null): array
    {
        $company = $company instanceof Company ? $company : Company::query()->findOrFail($company);

        $companyApprovalSettings = $this->companyApprovalSettings($company);

        $approversArray = $this->getRequestableRawApprovers($modelAlias, $companyApprovalSettings);

        foreach ($approversArray as &$approver) {

            if($approver['type'] == ApproverType::MANAGER){

                $finalEmployeeModel = $this->chainForeignPath($modelThroughForeign, $modelAlias);

                if(empty($finalEmployeeModel)){continue;}

                $approver['approver_id'] = $finalEmployeeModel?->manager?->user_id;
            }

            if($approver['type'] == ApproverType::DEPARTMENT_HEAD){

                $finalEmployeeModel = $this->chainForeignPath($modelThroughForeign, $modelAlias);

                if(empty($finalEmployeeModel)){continue;}

                $employeeDepartment = $finalEmployeeModel->departments?->first();

                if(empty($employeeDepartment)){continue;}

                $departmentHead = $employeeDepartment->employees()
                    ->wherePivot('department_assignment_type', DepartmentEmployeeAssignmentType::HEAD->value)
                    ->first();

                $approver['approver_id'] = $departmentHead?->user_id;
            }
        }

        $approversArray = $this->mapApprover($approversArray);
        $approversArray = $this->removeEmptyApprover($approversArray);
        if(!empty($requesterId)){
            $approversArray = $this->removeRequesterFromApprover($approversArray, $requesterId);
        }
        $approversArray = $this->removeDuplicateApproverAndKeepFirstOccurrence($approversArray);

        return $this->reOrderApprover($approversArray);
    }

    public function mapApprover($approversArray): array
    {
        return array_map(function($approver){
            return [
                'order' => $approver['order'],
                'approver_id' => $approver['approver_id']
            ];
        }, $approversArray);
    }

    public function removeEmptyApprover($approversArray): array
    {
        return array_filter($approversArray, function($approver){
            return !empty($approver['approver_id']);
        });
    }

    public function removeRequesterFromApprover($approversArray, $requesterId): array
    {
        return array_filter($approversArray, function($approver) use ($requesterId){
            return $approver['approver_id'] != $requesterId;
        });
    }

    public function removeDuplicateApproverAndKeepFirstOccurrence($approversArray): array
    {
        $unique = [];

        foreach ($approversArray as $approver) {
            if (!isset($unique[$approver['approver_id']])) {
                $unique[$approver['approver_id']] = $approver;
            }
        }

        return array_values($unique);
    }

    public function reOrderApprover($approversArray): array
    {
        $order = 1;

        return array_map(function($approver) use (&$order){
            $approver['order'] = $order++;
            return $approver;
        }, $approversArray);
    }
}
