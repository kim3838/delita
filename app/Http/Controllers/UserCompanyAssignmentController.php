<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Models\User;
use App\Transformers\CompanyAssignment\ListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UserCompanyAssignmentController extends Controller
{
    public function index(Request $request, $userUlid)
    {
        if($request->expectsJson()){

            $data = App::make(UserCompanyAssignmentRepository::class)->list($userUlid);

            return ResponseJson::successfulResponse(
                Fractal::collection($data, ListTransformer::class, 'company_assignment')
            );
        }

        abort(404);
    }

    public function sync(Request $request, $userId)
    {
        if($request->expectsJson()){

            $data = User::findOrFail($userId)->companies()->sync($request->all());

            return ResponseJson::successfulResponse($data);
        }

        abort(404);
    }
}
