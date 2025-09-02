<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static item($item, $transformer, boolean $meta = false)
 * @method static collection($collection, $transformer, string $key = null, boolean $meta = true)
 */
class Fractal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fractal';
    }
}
