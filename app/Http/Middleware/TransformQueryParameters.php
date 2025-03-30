<?php

namespace App\Http\Middleware;

use App\Actions\Common\FilterInteger;
use App\Actions\Common\NullOnEmptyString;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;
use Symfony\Component\HttpFoundation\Response;

class TransformQueryParameters
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $filters = [
            [
                'key' => 'page',
                'pipes' => [FilterInteger::class, NullOnEmptyString::class],
            ], [
                'key' => 'perPage',
                'pipes' => [FilterInteger::class, NullOnEmptyString::class],
            ],
        ];

        foreach ($filters as $filter){

            if(empty($filter['key']) || !$request->query->has($filter['key'])){
                continue;
            }

            $value = $request->query->get($filter['key']);

            $value = Pipeline::send($value)
                ->through($filter['pipes'])
                ->then(function($value) use($filter){
                    return $value;
                });

            $request->query->set($filter['key'], $value);
        }

        return $next($request);
    }
}
