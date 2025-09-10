<?php

namespace App\Http\Controllers\Imports;

use App\Blueprint\Imports\EmploymentProfileImport;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmploymentProfileController extends Controller
{
    public function __construct(
        protected EmploymentProfileImport $import
    ){}

    public function read(Request $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse($this->import->read($request));
        }

        abort(404);
    }

    public function reValidate(Request $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse($this->import->reValidate($request));
        }

        abort(404);
    }

    public function save(Request $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse($this->import->save($request));
        }

        abort(404);
    }
}
