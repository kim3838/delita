<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyRepository;
use App\Enums\CompanyUserAssignmentType;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Transformers\Company\ItemTransformer;
use App\Transformers\Company\ListTransformer;
use App\Transformers\Company\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class CompanyController extends Controller
{
    public function index()
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(CompanyRepository::class)->list(),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if($request->expectsJson()){

            $selection = App::make(CompanyRepository::class)->selection();
            $selected = Arr::first($selection);

            return ResponseJson::successfulResponse([
                ...Fractal::collection($selection, SelectionTransformer::class, 'selection'),
                'selected' => $selected?->id
            ]);
        }

        abort(404);
    }

    public function show(Request $request, $ulid)
    {
        if($request->expectsJson()){

            $company = App::make(CompanyRepository::class)->show($ulid);
            $company = $company ? Fractal::item($company, ItemTransformer::class) : $company;

            return ResponseJson::successfulResponse(['company' => $company]);
        }

        abort(404);
    }

    public function store(StoreCompanyRequest $request)
    {
        if($request->expectsJson()){

            $company = App::make(CompanyRepository::class)->store($request->validated());

            if($company){

                $request->user()->companies()->syncWithoutDetaching([
                    $company->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]
                ]);
            }

            return ResponseJson::successfulResponse([
                'company' => Fractal::item($company, ItemTransformer::class)
            ]);
        }

        abort(404);
    }

    public function update(UpdateCompanyRequest $request, $companyId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'company' => Fractal::item(
                    App::make(CompanyRepository::class)->update($companyId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }
}
