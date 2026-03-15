<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Leave\ListLeaveRequest;
use App\Transformers\Leave\ListTransformer;
use Carbon\Carbon;

class LeaveTodayAndUpcomingController extends Controller
{
    public function __construct(
        protected readonly LeaveRepository $repository
    ){}

    public function index(ListLeaveRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $dateTo = data_get($filters, 'date_to', Carbon::now()->toDateString());
            $dateTomorrow = Carbon::parse($dateTo)->copy()->addDay()->toDateString();

            $leavesToday = $this->repository->list($filters);

            $upcomingLeavesFilters = (object)(collect($filters)->except(['date_from', 'date_to'])->toArray());
            $upcomingLeavesFilters->date_from_onwards = $dateTomorrow;
            $upcomingLeaves = $this->repository->list($upcomingLeavesFilters, [], [
                ['field' => 'leaves.date', 'direction' => 'ASC'],
                ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ]);

            $leavesToday = Fractal::collection($leavesToday, ListTransformer::class)['data'];
            $upcomingLeaves = Fractal::collection($upcomingLeaves, ListTransformer::class)['data'];

            return ResponseJson::successfulResponse([
                'today_leaves' => $leavesToday,
                'upcoming_leaves' => $upcomingLeaves,
            ]);
        }

        abort(404);
    }
}
