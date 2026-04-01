<?php

namespace App\Http\Controllers;

use App\Blueprint\LeaveServiceInterface;
use App\Exceptions\UnexpectedException;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveDateRangeInquire\LeaveDateRangeInquireRequest;
use Carbon\CarbonPeriod;

class LeaveDateRangeInquireController extends Controller
{
    /**
     * @throws UnexpectedException
     */
    public function index(LeaveDateRangeInquireRequest $request)
    {
        if($request->expectsJson()){

            $companyId = $request->validated()['company_id'];
            $employeeId = $request->validated()['employee_id'];
            $shiftId = $request->validated()['shift_id'];
            $dateFrom = $request->validated()['date_from'];
            $dateTo = $request->validated()['date_to'];

            $datePeriod = CarbonPeriod::create($dateFrom, $dateTo);

            $leaveService = app(LeaveServiceInterface::class);

            $inquiredDates = $leaveService->leaveInquiryMap(
                $companyId,
                $employeeId,
                $shiftId,
                $datePeriod
            );

            return ResponseJson::successfulResponse([
                'dates' => $inquiredDates
            ]);
        }

        abort(404);
    }
}
