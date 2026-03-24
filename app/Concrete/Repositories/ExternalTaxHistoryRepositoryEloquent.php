<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\ExternalTaxHistoryRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\ExternalTaxHistory;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ExternalTaxHistoryRepositoryEloquent extends BaseRepositoryEloquent implements ExternalTaxHistoryRepository
{
    public function model(): string
    {
        return ExternalTaxHistory::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'external_tax_histories.employee_id');
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('external_tax_histories.remarks', 'LIKE', "%$value%");
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "employee_sub.number AS employee_number",
                "employee_sub.full_name AS employee_full_name",

                "external_tax_histories.id AS id",
                "external_tax_histories.ulid AS ulid",
                "external_tax_histories.employee_id AS employee_id",
                "external_tax_histories.year AS year",
                "external_tax_histories.total_taxable AS total_taxable",
                "external_tax_histories.total_nontaxable_bonus AS total_nontaxable_bonus",
                "external_tax_histories.total_taxable_from_bonus AS total_taxable_from_bonus",
                "external_tax_histories.total_tax_withheld AS total_tax_withheld",
                "external_tax_histories.remarks AS remarks",
            ]);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ['field' => 'external_tax_histories.year', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }

    public function update($identifier, $attributes)
    {
        $model = $this->show($identifier);

        $model->update($attributes);

        return $model;
    }
}
