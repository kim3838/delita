<?php

namespace App\Http\Controllers;

use App\Exceptions\UnexpectedException;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveDateRangeInquire\LeaveDateRangeInquireRequest;
use App\Traits\HasLeave;
use Carbon\CarbonPeriod;

class LeaveDateRangeInquireController extends Controller
{
    use HasLeave;

    /**
     * @throws UnexpectedException
     */
    public function index(LeaveDateRangeInquireRequest $request)
    {
        if($request->expectsJson()){

            $companyId = $request->validated()['company_id'];
            $employeeId = $request->validated()['employee_id'];
            $shiftId = $request->validated()['shift_id'];
            $leaveTypeId = $request->validated()['leave_type_id'];
            $dateFrom = $request->validated()['date_from'];
            $dateTo = $request->validated()['date_to'];

            $datePeriod = CarbonPeriod::create($dateFrom, $dateTo);

            $inquiredDates = $this->inquiryMap(
                $companyId,
                $employeeId,
                $shiftId,
                $leaveTypeId,
                $datePeriod
            );

            return ResponseJson::successfulResponse([
                'dates' => $inquiredDates
            ]);
        }

        abort(404);
    }
}
