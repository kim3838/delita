<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AccountRepository;
use App\Blueprint\Repositories\AccountSubscriptionRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Account\ListAccountRequest;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Account\ViewAccountRequest;
use App\Transformers\Account\ItemTransformer;
use App\Transformers\Account\ListTransformer;
use App\Transformers\Account\SelectionTransformer;
use App\Transformers\AccountSubscription\PatchableTransformer as AccountSubscriptionPatchableTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        protected readonly AccountRepository $repository,
        protected readonly AccountSubscriptionRepository $subscriptionRepository
    ){}

    public function index(ListAccountRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection($this->repository->paginate($filters), ListTransformer::class)
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection($this->repository->selection($filters), SelectionTransformer::class, 'selection')
            );
        }

        abort(404);
    }

    public function show(ViewAccountRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $account = $this->repository->show($ulid);

            $subscriptions = $account->subscriptions->isEmpty()
                ? []
                : Fractal::collection($account->subscriptions->sortBy(function($item, $key){
                    return $item->module->value;
                }, SORT_NUMERIC), AccountSubscriptionPatchableTransformer::class)['data'];

            $account = $account ? Fractal::item($account, ItemTransformer::class) : $account;

            return ResponseJson::successfulResponse([
                'account' => $account,
                'subscriptions' => $subscriptions
            ]);
        }

        abort(404);
    }

    public function showGate(ViewAccountRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $account = $this->repository->showAndTransformToBasic($ulid);

            return ResponseJson::successfulResponse(['account' => $account]);
        }

        abort(404);
    }

    public function store(StoreAccountRequest $request)
    {
        if($request->expectsJson()){

            $data = array_merge($request->validated(), ['date_registered' => Carbon::now()->toDateTimeString()]);

            $account = $this->repository->store($data);

            $subscriptions = collect($request->validated()['subscriptions'])->map(function ($subscription){
                return [
                    'module' => $subscription['module'],
                    'plan' => $subscription['plan'],
                    'date_subscribed' => Carbon::now()->toDateTimeString()
                ];
            });

            $account->subscriptions()->createMany($subscriptions->toArray());

            return ResponseJson::successfulResponse([
                'account' => Fractal::item($account, ItemTransformer::class)
            ]);
        }

        abort(404);
    }

    public function update(UpdateAccountRequest $request, $accountId)
    {
        if($request->expectsJson()){

            if(!empty($request->validated()['spliced_subscriptions'])){
                $this->subscriptionRepository->batchDelete($request->validated()['spliced_subscriptions']);
            }

            $account = $this->repository->update($accountId, $request->validated());

            $subscriptions = collect($request->validated()['subscriptions'])->filter(function ($subscription){
                return isset($subscription['id']) && $subscription['id'] != null;
            })->map(function ($subscription){
                return [
                    'id' => $subscription['id'],
                    'account_id' => $subscription['account_id'],
                    'module' => $subscription['module'],
                    'plan' => $subscription['plan'],
                ];
            });

            foreach($subscriptions as $subscription){
                $this->subscriptionRepository->update($subscription['id'], $subscription);
            }

            $newSubscriptions = collect($request->validated()['subscriptions'])->filter(function ($subscription){
                return !isset($subscription['id']) || $subscription['id'] == null;
            })->map(function ($subscription){
                return [
                    'module' => $subscription['module'],
                    'plan' => $subscription['plan'],
                    'date_subscribed' => Carbon::now()->toDateTimeString()
                ];
            });

            $account->subscriptions()->createMany($newSubscriptions->toArray());

            return ResponseJson::successfulResponse([
                'account' => Fractal::item($account, ItemTransformer::class)
            ]);
        }

        abort(404);
    }
}
