<?php

namespace App\Http\Controllers;

use App\Concrete\AutoCreateAttendanceConcrete;
use App\Exceptions\UnexpectedException;
use App\Facades\ResponseJson;
use App\Http\Requests\AutoCreateAttendance\StoreAutoCreateAttendanceRequest;
use App\Models\Company;

class AutoCreateAttendanceController extends Controller
{
    protected ?Company $company;

    /**
     * @throws UnexpectedException
     */
    public function store(StoreAutoCreateAttendanceRequest $request)
    {
        if($request->expectsJson()){

            $autoCreateAttendance = new AutoCreateAttendanceConcrete();

            $errors = $autoCreateAttendance($request->validated());

            return ResponseJson::successfulResponse([
                'errors' => $errors,
            ]);
        }

        abort(404);
    }
}
