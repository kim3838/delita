<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Transformers\AssociatedCompany\ItemTransformer;
use App\Transformers\AssociatedCompany\ListTransformer;
use App\Transformers\AssociatedCompany\SelectionTransformer;
use Illuminate\Support\Facades\Request;

class AssociatedCompanyController extends Controller
{
    public function __construct(
        protected AssociatedCompanyRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request::expectsJson()){

            $filters = json_decode(Request::get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    $this->repository->paginate($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if($request::expectsJson()){

            $filters = json_decode(Request::get('filters'));

            $selection = $this->repository->selection($filters);

            $selected = $selection->first();

            return ResponseJson::successfulResponse([
                ...Fractal::collection($selection, SelectionTransformer::class, 'selection'),
                'selected' => $selected?->company_id
            ]);
        }

        abort(404);
    }

    public function show(Request $request, $ulid)
    {
        if($request::expectsJson()){

            $company = $this->repository->show($ulid);

            $company = $company ? Fractal::item($company, ItemTransformer::class) : $company;

            return ResponseJson::successfulResponse(['company' => $company]);
        }

        abort(404);
    }

    public function update(UpdateCompanyRequest $request, $companyId)
    {
        if($request->expectsJson()){

            $company = $this->repository->update($companyId, $request->validated());

            $company = $company ? Fractal::item($company, ItemTransformer::class) : $company;

            return ResponseJson::successfulResponse([
                'company' => $company
            ]);
        }

        abort(404);
    }
}
