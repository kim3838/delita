<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static item($item, $transformer, boolean $meta = false)
 * @method static collection($collection, $transformer, boolean $meta = true, string $key = null)
 */
class Fractal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fractal';
    }
}
