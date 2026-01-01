<?php

namespace App\Http\Controllers;

use App\Facades\ResponseJson;
use App\Http\Requests\LeaveRunningBalance\LeaveRunningBalanceRequest;

class LeaveRunningBalanceController extends Controller
{
    public function indexGate(LeaveRunningBalanceRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
