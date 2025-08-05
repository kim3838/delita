<?php

namespace App\Http\Controllers;

use App\Facades\ResponseJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class OrderableController extends Controller
{
    public function reOrder(Request $request, $module)
    {
        if(request()->expectsJson()){

            $orderables = json_decode($request->get('orderables'));

            App::make($module)->reOrder($orderables);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
