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
        return $this->model::query()->create($attributes);
    }

    public function show($identifier)
    {
        return $this->model::query()->findOrfail($identifier);
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

    public function update($identifier, $attributes)
    {
        $model = $this->model::query()->findOrfail($identifier);

        $model->update($attributes);

        return $model;
    }

    public function delete($identifier): ?bool
    {
        $model = $this->model::query()->findOrfail($identifier);

        return $model->delete();
    }

    public function batchDelete($ids): int
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
    protected function hydratePaginationItems(LengthAwarePaginator $paginator, Model|string $fullyQualifiedClassName): LengthAwarePaginator
    {
        $items = [];

        foreach ($paginator->items() as $key => $item) {
            $items[] = (get_class($item) === 'stdClass')
                ? (array)$item
                : $item->toArray();
        }

        return $paginator->setCollection($fullyQualifiedClassName::query()->hydrate($items));
    }

    public function hydrateItem(array|object $attributes = [])
    {
        return $this->model::query()->hydrate([$attributes])->first();
    }

    /**
     * Hydrate collection to the specified class
     *
     * @param Collection $collection
     * @param Model $class
     * @return Collection
     */
    protected function hydrateCollection(Collection $collection, Model|string $fullyQualifiedClassName): Collection
    {
        $items = [];

        foreach ($collection as $key => $item) {
            $items[] = (get_class($item) === 'stdClass')
                ? (array)$item
                : $item->toArray();
        }

        return $fullyQualifiedClassName::query()->hydrate($items);
    }

    public function reOrder($orderables): void
    {
        foreach ($orderables as $orderable) {
            $this->model::query()->find($orderable->id)->update(['order' => $orderable->order]);
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

    protected function queryAsSub(Builder $builder, $as)
    {
        return app(Builder::class)->fromSub($builder, $as);
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
