<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AccountRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Account\ListAccountRequest;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Account\ViewAccountRequest;
use App\Transformers\Account\ItemTransformer;
use App\Transformers\Account\ListTransformer;
use App\Transformers\Account\SelectionTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AccountController extends Controller
{
    public function index(ListAccountRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(AccountRepository::class)->list($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(AccountRepository::class)->selection($filters),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }

    public function show(ViewAccountRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $account = App::make(AccountRepository::class)->show($ulid);
            $account = $account ? Fractal::item($account, ItemTransformer::class) : $account;

            return ResponseJson::successfulResponse(['account' => $account]);
        }

        abort(404);
    }

    public function check(ViewAccountRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $account = App::make(AccountRepository::class)->check($ulid);

            return ResponseJson::successfulResponse(['account' => $account]);
        }

        abort(404);
    }

    public function store(StoreAccountRequest $request)
    {
        if($request->expectsJson()){

            $data = array_merge(
                $request->validated(),
                ['date_registered' => Carbon::now()->toDateTimeString()]
            );

            return ResponseJson::successfulResponse([
                'account' => Fractal::item(
                    App::make(AccountRepository::class)->store($data),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateAccountRequest $request, $accountId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'account' => Fractal::item(
                    App::make(AccountRepository::class)->update($accountId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }
}
