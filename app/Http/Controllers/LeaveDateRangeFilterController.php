<?php

namespace App\Http\Controllers;

use App\Exceptions\UnexpectedException;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveDateRangeFilter\LeaveDateRangeFilterRequest;
use App\Traits\HasLeave;
use Carbon\CarbonPeriod;

class LeaveDateRangeFilterController extends Controller
{
    use HasLeave;

    /**
     * @throws UnexpectedException
     */
    public function index(LeaveDateRangeFilterRequest $request)
    {
        if($request->expectsJson()){

            $companyId = $request->validated()['company_id'];
            $employeeId = $request->validated()['employee_id'];
            $shiftId = $request->validated()['shift_id'];
            $dateFrom = $request->validated()['date_from'];
            $dateTo = $request->validated()['date_to'];

            $datePeriod = CarbonPeriod::create($dateFrom, $dateTo);

            $filteredDates = $this->filterLeaveDateRange(
                $companyId,
                $employeeId,
                $shiftId,
                $datePeriod
            );

            return ResponseJson::successfulResponse([
                'dates' => $filteredDates
            ]);
        }

        abort(404);
    }
}
