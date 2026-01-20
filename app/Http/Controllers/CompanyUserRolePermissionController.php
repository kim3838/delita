<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyUserRolePermissionRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyUserRolePermission\ListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CompanyUserRolePermissionController extends Controller
{
    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(CompanyUserRolePermissionRepository::class)->list($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }
}
