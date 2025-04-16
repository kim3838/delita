<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\AssociatedCompany\SelectionTransformer;
use Illuminate\Support\Arr;

class AssociatedCompanyController extends Controller
{
    public function __construct(
        protected AssociatedCompanyRepository $repository
    ){}

    public function index()
    {
        if(request()->expectsJson()){

            $selection = $this->repository->selection();

            $selected = Arr::first($selection);

            return ResponseJson::successfulResponse([
                ...Fractal::collection($selection, SelectionTransformer::class, 'selection'),
                'selected' => $selected?->company_id
            ]);
        }

        abort(404);
    }
}
