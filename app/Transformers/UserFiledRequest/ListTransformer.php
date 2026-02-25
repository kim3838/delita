<?php

namespace App\Transformers\UserFiledRequest;

use App\Enums\RequestableType;
use App\Models\Hydrations\User\UserFiledRequest;
use App\Traits\HasTime;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    use HasTime;

    public function transform(UserFiledRequest $userFiledRequest): array
    {
        return [
            'row_number' => $userFiledRequest->row_number,
            'id' => $userFiledRequest->id,
            'requestable_type' => $userFiledRequest->requestable_type,
            'requestable_id' => $userFiledRequest->requestable_id,
            'requestable_type_readable' => RequestableType::tryFrom($userFiledRequest->requestable_type)?->label(),
            'number' => $userFiledRequest->number,
            'date_requested' => $userFiledRequest->date_requested->format('Y-m-d H:i'),
            'date_requested_diff' => $this->diffForHumans(
                $userFiledRequest->date_requested->shiftTimezone($userFiledRequest->company_timezone),
                Carbon::now($userFiledRequest->company_timezone)
            ),
            'company_timezone' => $userFiledRequest->company_timezone,
            'reason' => $userFiledRequest->reason,
            'status_summary' => $userFiledRequest->status_summary?->toArray(),

            'requested_by' => [
                'company_id' => $userFiledRequest->user_company_id,
                'company_name' => $userFiledRequest->company_name,
                'company_assignment_type' => $userFiledRequest->company_assignment_type?->toArray(),
                'is_employee' => $userFiledRequest->is_employee,
                'company_employee_number' => $userFiledRequest->company_employee_number,
                'company_employee_full_name' => implode(' ', array_filter([
                    $userFiledRequest->company_employee_family_name,
                    $userFiledRequest->company_employee_given_name,
                    $userFiledRequest->company_employee_middle_name
                ])),

                'id' => $userFiledRequest->user_id,
                'ulid' => $userFiledRequest->user_ulid,
                'username' => $userFiledRequest->user_username,
                'email' => $userFiledRequest->user_email,
                'status' => $userFiledRequest->user_status?->toArray(),
                'email_verified_at' => $userFiledRequest->user_email_verified_at,
                'timezone' => $userFiledRequest->user_timezone,
            ]
        ];
    }
}
