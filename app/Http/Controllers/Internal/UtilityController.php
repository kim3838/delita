<?php

namespace App\Http\Controllers\Internal;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function hit(Request $request)
    {
        return ResponseJson::successfulResponse();
    }

    public function post(Request $request)
    {
        return ResponseJson::successfulResponse();
    }

    public function debug(Request $request)
    {
        return ResponseJson::successfulResponse();
    }
}
