<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyAssignment\ListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UserCompanyAssignmentController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $data = App::make(UserCompanyAssignmentRepository::class)->list($filters);

            return ResponseJson::successfulResponse(
                Fractal::collection($data, ListTransformer::class, 'company_assignment')
            );
        }

        abort(404);
    }

    public function sync(Request $request, $userId)
    {
        if($request->expectsJson()){

            $data = App::make(UserCompanyAssignmentRepository::class)->sync($userId, $request->all());

            return ResponseJson::successfulResponse($data);
        }

        abort(404);
    }
}
