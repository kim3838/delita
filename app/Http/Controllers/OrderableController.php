<?php

namespace App\Http\Controllers;

use App\Facades\ResponseJson;
use Illuminate\Support\Facades\App;

class OrderableController extends Controller
{
    public function reOrder($module)
    {
        if(request()->expectsJson()){

            App::make($module)->reOrder();

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
