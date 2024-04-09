<?php

namespace App\Concrete;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;

class FractalTransformer
{
    protected function item($item, $transformer, $meta = false): array
    {
        $fractal = new Fractal\Manager();

        if(!$meta){$fractal->setSerializer(new ArraySerializer());}

        $data = new Item($item, new $transformer);

        $item = $fractal->createData($data)->toArray();

        return $item;
    }

    protected function collection($collection, $transformer, $meta = true, $key = null): array
    {
        if (($collection instanceof Collection && $collection->count() === 0)
            || (is_array($collection) && count($collection) === 0)
        ) {
            return [$key ?? 'data' => []];
        }

        $fractal = new Fractal\Manager();

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
