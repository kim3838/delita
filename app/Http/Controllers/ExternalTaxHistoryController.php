<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\ExternalTaxHistoryRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\ExternalTaxHistory\BatchDestroyExternalTaxHistoryRequest;
use App\Http\Requests\ExternalTaxHistory\ListExternalTaxHistoryRequest;
use App\Http\Requests\ExternalTaxHistory\StoreExternalTaxHistoryRequest;
use App\Http\Requests\ExternalTaxHistory\UpdateExternalTaxHistoryRequest;
use App\Transformers\ExternalTaxHistory\ListTransformer;

class ExternalTaxHistoryController extends Controller
{
    public function __construct(
        protected readonly ExternalTaxHistoryRepository $repository
    ){}

    public function indexGate(ListExternalTaxHistoryRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function index(ListExternalTaxHistoryRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    public function store(StoreExternalTaxHistoryRequest $request)
    {
        if($request->expectsJson()){

            $this->repository->store($request->validated());

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function update(UpdateExternalTaxHistoryRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $this->repository->update($ulid, $request->validated());

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyExternalTaxHistoryRequest $request)
    {
        if($request->expectsJson()){

            $ids = data_get($request->validated(), 'external_tax_history_ids', []);

            $this->repository->batchDelete($ids);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
