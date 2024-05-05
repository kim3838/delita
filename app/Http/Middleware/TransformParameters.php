<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Pipeline;
use Symfony\Component\HttpFoundation\Response;

class TransformParameters
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $filters = [[
            'key' => 'page',
            'filter_options' => [FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH]
        ], [
            'key' => 'perPage',
            'filter_options' => [FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH]
        ]];

        foreach ($filters as $filter){

            if(empty($filter['key'])){
                continue;
            }

            $valueToFilter = $request->{$filter['key']};

            $valueToFilter = Pipeline::send($valueToFilter)->through([
                function($valueToFilter, Closure $next) use($filter){

                    if(Arr::has($filter, 'filter_options')){
                        $valueToFilter = call_user_func_array(
                            'filter_var',
                            array_merge([$valueToFilter], $filter['filter_options'])
                        );
                    }

                    return $next($valueToFilter);
                }
            ])->then(function($valueToFilter) use($filter){
                return json_decode($valueToFilter);
            });

            $request->merge([
                ($filter['key']) => $valueToFilter
            ]);
        }

        return $next($request);
    }
}
