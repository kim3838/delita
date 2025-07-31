<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeContactRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeeContact\StoreEmployeeContactRequest;
use App\Http\Requests\EmployeeContact\UpdateEmployeeContactRequest;
use App\Transformers\EmployeeContact\ItemTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmployeeContactController extends Controller
{
    public function validate(StoreEmployeeContactRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employee_contact' => Fractal::item(
                    App::make(EmployeeContactRepository::class)->hydrateItem($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StoreEmployeeContactRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employee_contact' => Fractal::item(
                    App::make(EmployeeContactRepository::class)->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function show(Request $request, $employeeId)
    {
        if($request->expectsJson()){

            $employeeContact = App::make(EmployeeContactRepository::class)->show($employeeId);
            $employeeContact = $employeeContact ? Fractal::item($employeeContact, ItemTransformer::class) : $employeeContact;

            return ResponseJson::successfulResponse(['employee_contact' => $employeeContact]);
        }

        abort(404);
    }

    public function update(UpdateEmployeeContactRequest $request, $employeeId)
    {
        if($request->expectsJson()){

            $employeeContact = App::make(EmployeeContactRepository::class)->update($employeeId, $request->validated());
            $employeeContact = $employeeContact ? Fractal::item($employeeContact, ItemTransformer::class) : $employeeContact;

            return ResponseJson::successfulResponse(['employee_contact' => $employeeContact]);
        }

        abort(404);
    }
}
