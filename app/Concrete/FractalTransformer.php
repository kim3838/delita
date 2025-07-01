<?php

namespace App\Concrete;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;

class FractalTransformer
{
    public function item($item, $transformer, $meta = false): array
    {
        $item = clone $item;

        $fractal = new FractalManager();

        if(!$meta){$fractal->setSerializer(new ArraySerializer());}

        $data = new Item($item, new $transformer);

        return $fractal->createData($data)->toArray();
    }

    public function collection($collection, $transformer, $key = null, $meta = true): array
    {
        $collection = clone $collection;

        if (($collection instanceof Collection && $collection->count() === 0)
            || (is_array($collection) && count($collection) === 0)
        ) {
            return [$key ?? 'data' => []];
        }

        $fractal = new FractalManager();

        $data = new Collection($collection, new $transformer);

        if ($meta && ($collection instanceof LengthAwarePaginator)) {
            $data->setPaginator(new IlluminatePaginatorAdapter($collection));
        }

        $collection = $fractal->createData($data)->toArray();

        if (!is_null($key)) {
            $collection[$key] = $collection['data'];
            unset($collection['data']);
        }

        return $collection;
    }
}
