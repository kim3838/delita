<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\UserRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\User\ListUserRequest;
use App\Http\Requests\User\StoreAutogenerateUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\ViewUserRequest;
use App\Transformers\User\ItemTransformer;
use App\Transformers\User\ListTransformer;
use App\Transformers\User\PatchableTransformer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function autoGenerateValidate(StoreAutogenerateUserRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'user' => null
            ]);
        }

        abort(404);
    }

    public function autoGenerate(StoreAutogenerateUserRequest $request)
    {
        if($request->expectsJson()){

            $user = App::make(UserRepository::class)->autoGenerate($request->validated());
            $user = $user ? Fractal::item($user, ItemTransformer::class) : $user;

            return ResponseJson::successfulResponse(['user' => $user]);
        }

        abort(404);
    }

    public function validate(StoreUserRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'user' => Fractal::item(
                    App::make(UserRepository::class)->hydrateItem($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function indexGate(ListUserRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function index(ListUserRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(UserRepository::class)->paginate($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function store(StoreUserRequest $request)
    {
        if($request->expectsJson()){

            $data = array_merge($request->validated(), [
                'created_by' => $request->user()->id,
                'remember_token' => Str::random(10),
                'pre_hash_password' => $request->validated()['password'],
                'password' => Hash::make($request->validated()['password'])
            ]);

            $roles = data_get($request->validated(), 'role_ids', []);

            $user = App::make(UserRepository::class)->store($data);

            $user->syncRoles($roles);

            return ResponseJson::successfulResponse([
                'user' => Fractal::item($user, ItemTransformer::class)
            ]);
        }

        abort(404);
    }

    public function show(ViewUserRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $user = App::make(UserRepository::class)->show($ulid);
            $user = $user ? Fractal::item($user, PatchableTransformer::class) : $user;

            return ResponseJson::successfulResponse(['user' => $user]);
        }

        abort(404);
    }

    public function showGate(ViewUserRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $user = App::make(UserRepository::class)->showAndTransformToBasic($ulid);

            return ResponseJson::successfulResponse(['user' => $user]);
        }

        abort(404);
    }

    public function update(UpdateUserRequest $request, $userId)
    {
        if($request->expectsJson()){

            $roles = data_get($request->validated(), 'role_ids', []);

            $user = App::make(UserRepository::class)->update($userId, $request->validated());

            $user->syncRoles($roles);

            return ResponseJson::successfulResponse([
                'user' => Fractal::item($user, ItemTransformer::class)
            ]);
        }

        abort(404);
    }
}
