<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\LeaveRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Transformers\Leave\ListTransformer;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        protected readonly LeaveRepository $repository
    ){}


    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $relations = [];
            $orders = [
                ['field' => 'leaves.date', 'direction' => 'DESC'],
            ];

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, $relations, $orders),
                ListTransformer::class
            ));
        }

        abort(404);
    }
}
