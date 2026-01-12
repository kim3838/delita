<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatementModule\DestroySalaryStatementModuleRequest;
use App\Http\Requests\SalaryStatementModule\ReOrderSalaryStatementModuleRequest;
use App\Http\Requests\SalaryStatementModule\StoreSalaryStatementModuleRequest;
use App\Http\Requests\SalaryStatementModule\UpdateSalaryStatementModuleRequest;
use App\Transformers\SalaryStatementModule\BasicTransformer;
use App\Transformers\SalaryStatementModule\ItemTransformer;
use App\Transformers\SalaryStatementModule\ListTransformer;
use App\Transformers\SalaryStatementModule\PatchableTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SalaryStatementModuleController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $transformer = $request->user()->isSuperAdmin()
                ? ListTransformer::class
                : BasicTransformer::class;

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(SalaryStatementModuleRepository::class)->list($filters),
                $transformer,
                'salary_statement_modules'
            ));
        }

        abort(404);
    }

    public function store(StoreSalaryStatementModuleRequest $request)
    {
        if($request->expectsJson()){

            $hydrated = App::make(SalaryStatementModuleRepository::class)->hydrateItem($request->validated());
            $patchable = Fractal::item($hydrated, PatchableTransformer::class);

            return ResponseJson::successfulResponse([
                'salary_statement_module' => Fractal::item(
                    App::make(SalaryStatementModuleRepository::class)->store($patchable),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateSalaryStatementModuleRequest $request, $salaryStatementModuleId)
    {
        if($request->expectsJson()){

            $hydrated = App::make(SalaryStatementModuleRepository::class)->hydrateItem($request->validated());
            $patchable = Fractal::item($hydrated, PatchableTransformer::class);

            return ResponseJson::successfulResponse([
                'salary_statement_module' => Fractal::item(
                    App::make(SalaryStatementModuleRepository::class)->update($salaryStatementModuleId, $patchable),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroySalaryStatementModuleRequest $request, $salaryStatementModuleId)
    {
        if($request->expectsJson()){

            App::make(SalaryStatementModuleRepository::class)->delete($salaryStatementModuleId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function reOrder(ReOrderSalaryStatementModuleRequest $request)
    {
        if($request->expectsJson()){

            $orderables = json_decode($request->get('orderables'));

            App::make(SalaryStatementModuleRepository::class)->reOrder($orderables);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
