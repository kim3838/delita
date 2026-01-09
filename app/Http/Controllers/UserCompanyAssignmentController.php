<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\UserCompanyAssignment\SyncUserCompanyAssignment;
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

    public function sync(SyncUserCompanyAssignment $request, $userId)
    {
        if($request->expectsJson()){

            $assignments = data_get($request->all(), 'assignments', []);

            $data = App::make(UserCompanyAssignmentRepository::class)->sync($userId, $assignments);

            return ResponseJson::successfulResponse($data);
        }

        abort(404);
    }
}
