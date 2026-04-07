<?php

namespace App\Http\Middleware;

use App\Blueprint\RequestInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestBoot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestPayload = $request->get('payload');
        $requestPayload = is_string($requestPayload) ? json_decode($requestPayload) : (object)[];

        $requestFilters = $request->get('filters');
        $requestFilters = is_string($requestFilters) ? json_decode($requestFilters) : (object)[];

        $requestOrders = $request->get('orders');
        $requestOrders = is_string($requestOrders) ? json_decode($requestOrders) : (object)[];

        $requestInterface = app(RequestInterface::class);

        $requestInterface->accountId = $request->get('account_id');
        $requestInterface->companyId = $request->get('company_id');
        $requestInterface->payload = $requestPayload;
        $requestInterface->filters = $requestFilters;
        $requestInterface->orders = $requestOrders;

        return $next($request);
    }
}
