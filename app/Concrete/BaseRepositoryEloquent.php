<?php

namespace App\Concrete;

use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

abstract class BaseRepositoryEloquent
{
    protected Application $app;

    protected Model $model;

    protected string $modelAlias;

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     */
    public function __construct(Application $app)
    {
        try {

            $this->app = $app;
            $this->makeModel();
            $this->makeModelAlias();
        } catch (UnexpectedException|BindingResolutionException $e) {

        }
    }

    /**
     * @throws UnexpectedException
     * @throws BindingResolutionException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if(!$model instanceof Model){
            throw new UnexpectedException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    public function makeModelAlias(): false|int|string
    {
        $morphMap = Relation::morphMap();

        return $this->modelAlias = array_search($this->model(), $morphMap, true);
    }

    public function store($attributes)
    {
        return $this->model::create($attributes);
    }

    public function show($id)
    {
        return $this->model::findOrfail($id);
    }

    //Minimal version of show
    public function check($identifier)
    {
        $model = $this->show($identifier);

        $transformer = $this->app->make('Transformer', [
            'module' => $this->modelAlias,
            'type' => 'basic'
        ]);

        return $model ? Fractal::item($model, $transformer) : $model;
    }

    public function update($id, $attributes)
    {
        $model = $this->model::findOrfail($id);

        $model->update($attributes);

        return $model;
    }

    public function delete($id)
    {
        $model = $this->model::findOrfail($id);

        return $model->delete();
    }

    public function batchDelete($ids)
    {
        return $this->model::destroy($ids);
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

    public function hydrateItem(array|object $attributes = [])
    {
        return $this->model::hydrate([$attributes])->first();
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

    public function reOrder($orderables): void
    {
        foreach ($orderables as $orderable) {
            $this->model::find($orderable->id)->update(['order' => $orderable->order]);
        }
    }

    protected function rowNumberOrder($orders): string
    {
        if (empty($orders)) {
            return '';
        }

        $order = collect($orders)
            ->map(fn ($o) => "{$o['field']} {$o['direction']}")
            ->implode(', ');

        return "ORDER BY $order";
    }

    protected function setOrdersOnBuilder(Builder $builder, $orders = []): void
    {
        foreach ($orders as $order) {
            $builder->orderBy($order['field'], $order['direction']);
        }
    }

    protected function setGroupsOnBuilder(Builder $builder, $groups = []): void
    {
        foreach ($groups as $group) {
            $builder->groupBy($group);
        }
    }

    protected function subQuery($builder, $alias = '_'): Builder
    {
        return DB::table(DB::raw("(" . $builder->toSql() . ") as " . $alias))
            ->mergeBindings($builder)
            ->select('*');
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
