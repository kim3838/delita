<?php

namespace App\Concrete;

use App\Exceptions\RepositoryException;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

abstract class BaseRepositoryEloquent
{
    protected $app;

    protected $model;

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model();

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if(!$model instanceof Model){
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Generate a LengthAwarePaginator class based from the count of Builder class
     *
     * @param Builder $queryBuilder
     * @return LengthAwarePaginator
     */
    protected function createPaginationFromBuilder(Builder $queryBuilder): LengthAwarePaginator
    {
        $itemsPerPage = Request::get('perPage', 50);

        $itemsPerPage = ($itemsPerPage == 0 ? 50 : $itemsPerPage);

        $page = Request::get('page', 1);

        $count = $queryBuilder->getCountForPagination();

        $data = $queryBuilder->forPage(
            $page,
            $itemsPerPage
        )->get();

        return new LengthAwarePaginator(
            $data,
            $count,
            $itemsPerPage,
            $page
        );
    }

    /**
     * Hydrate items in the paginator collection to the specified class
     *
     * @param LengthAwarePaginator $paginator
     * @param Model $class
     * @return LengthAwarePaginator
     */
    protected function hydratePaginationItems(LengthAwarePaginator $paginator, Model $class): LengthAwarePaginator
    {
        $items = [];

        foreach ($paginator->items() as $key => $item) {
            $items[] = (get_class($item) === 'stdClass')
                ? (array)$item
                : $item->toArray();
        }

        return $paginator->setCollection($class::hydrate($items));
    }

    /**
     * Hydrate collection to the specified class
     *
     * @param Collection $collection
     * @param Model $class
     * @return Collection
     */
    protected function hydrateCollection(Collection $collection, Model $class): Collection
    {
        $items = [];

        foreach ($collection as $key => $item) {
            $items[] = (get_class($item) === 'stdClass')
                ? (array)$item
                : $item->toArray();
        }

        return $class::hydrate($items);
    }

    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }
}
