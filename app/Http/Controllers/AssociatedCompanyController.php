<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\AssociatedCompany\ListTransformer;
use App\Transformers\AssociatedCompany\SelectionTransformer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

class AssociatedCompanyController extends Controller
{
    public function __construct(
        protected AssociatedCompanyRepository $repository
    ){}

    public function index()
    {
        if(request()->expectsJson()){

            $filters = json_decode(Request::get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    $this->repository->list($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function selection()
    {
        if(request()->expectsJson()){

            $filters = json_decode(Request::get('filters'));

            $selection = $this->repository->selection($filters);

            $selected = Arr::first($selection);

            return ResponseJson::successfulResponse([
                ...Fractal::collection($selection, SelectionTransformer::class, 'selection'),
                'selected' => $selected?->company_id
            ]);
        }

        abort(404);
    }
}
