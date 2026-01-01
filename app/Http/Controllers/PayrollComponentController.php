<?php

namespace App\Http\Controllers;

use App\Facades\ResponseJson;
use App\Http\Requests\PayrollComponent\ListPayrollComponentRequest;

class PayrollComponentController extends Controller
{
    public function indexGate(ListPayrollComponentRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
