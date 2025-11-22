<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\HolidayRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Holiday\BatchDestroyHolidayRequest;
use App\Http\Requests\Holiday\StoreHolidayRequest;
use App\Http\Requests\Holiday\UpdateHolidayRequest;
use App\Transformers\Holiday\ItemTransformer;
use App\Transformers\Holiday\ListTransformer;
use App\Transformers\Holiday\SelectionTransformer;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function __construct(
        protected readonly HolidayRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    $this->repository->selection($filters),
                    SelectionTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StoreHolidayRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'holiday' => Fractal::item(
                    $this->repository->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateHolidayRequest $request, $holidayUlid)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'holiday' => Fractal::item(
                    $this->repository->update($holidayUlid, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyHolidayRequest $request)
    {
        if($request->expectsJson()){

            $holidayIds = data_get($request->validated(), 'holiday_ids', []);

            $this->repository->batchDelete($holidayIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
