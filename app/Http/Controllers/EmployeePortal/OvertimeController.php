<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\OvertimeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Transformers\Overtime\ListTransformer;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function __construct(
        protected readonly OvertimeRepository $repository
    ){}

    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $relations = [];
            $orders = [
                ['field' => 'date', 'direction' => 'ASC'],
            ];

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, $relations, $orders),
                ListTransformer::class
            ));
        }

        abort(404);
    }
}
