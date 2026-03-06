<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Facades\Fractal;
use App\Models\EmployeePayrollComponent;
use App\Transformers\EmployeePayrollComponent\PatchableTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmployeePayrollComponentRepositoryEloquent extends BaseRepositoryEloquent implements EmployeePayrollComponentRepository
{
    public function model(): string
    {
        return EmployeePayrollComponent::class;
    }

    public function baseQueryBuilder($filters, $orders = [])
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'employee_payroll_components.employee_id');
            })
            ->leftJoin('compensations', function ($join) {
                $join->on('compensations.id', '=', 'employee_payroll_components.payroll_componentable_id')
                    ->where('employee_payroll_components.payroll_componentable_type', 'compensation');
            })
            ->leftJoin('deductions', function ($join) {
                $join->on('deductions.id', '=', 'employee_payroll_components.payroll_componentable_id')
                    ->where('employee_payroll_components.payroll_componentable_type', 'deduction');
            })
            ->leftJoin('income_taxes', function ($join) {
                $join->on('income_taxes.id', '=', 'employee_payroll_components.payroll_componentable_id')
                    ->where('employee_payroll_components.payroll_componentable_type', 'income_tax');
            })
            ->when(!empty($filters->payroll_componentable_type) && is_array($filters->payroll_componentable_type), function ($builder) use ($filters) {
                $builder->whereIn('employee_payroll_components.payroll_componentable_type', $filters->payroll_componentable_type);
            })
            ->when(!empty($filters->pay_types) && is_array($filters->pay_types), function ($builder) use ($filters) {
                $builder->whereIn('employee_payroll_components.pay_type', $filters->pay_types);
            })
            ->select([
                "employee_sub.number AS employee_number",
                DB::raw("
                    CASE
                        WHEN employee_payroll_components.payroll_componentable_type = 'compensation' THEN compensations.component_sub_type
                        WHEN employee_payroll_components.payroll_componentable_type = 'deduction' THEN deductions.component_sub_type
                    ELSE income_taxes.component_sub_type
                    END AS component_sub_type
                "),
                DB::raw("
                    CASE
                        WHEN employee_payroll_components.payroll_componentable_type = 'compensation' THEN compensations.type
                        WHEN employee_payroll_components.payroll_componentable_type = 'deduction' THEN deductions.type
                    ELSE income_taxes.type
                    END AS payroll_componentable_morph_to_type
                "),
                "employee_payroll_components.*",
                ...(isset($filters->payroll_componentable_date) ? [
                    DB::raw("'".$filters->payroll_componentable_date."' AS payroll_attendance_date"),
                ] : []),
            ]);

        $queryBuilder = $this->queryAsSub($queryBuilder, 'payroll_component_sub')
            ->when(!empty($filters->payroll_componentable_morph_to_type) && is_array($filters->payroll_componentable_morph_to_type), function ($builder) use ($filters) {
                $builder->whereIn('payroll_component_sub.payroll_componentable_morph_to_type', $filters->payroll_componentable_morph_to_type);
            })
            ->when(!empty($filters->payroll_componentable_component_sub_types) && is_array($filters->payroll_componentable_component_sub_types), function ($builder) use ($filters) {
                $builder->whereIn('payroll_component_sub.component_sub_type', $filters->payroll_componentable_component_sub_types);
            })
            ->when(!empty($filters->formulable_types) && is_array($filters->formulable_types), function ($builder) use ($filters) {
                $builder->whereIn('payroll_component_sub.formulable_type', $filters->formulable_types);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "payroll_component_sub.*",
            ])->when(isset($filters->payroll_componentable_date), function ($builder) use($filters) {
                $builder->where(function ($query) {
                        $query->whereNull('payroll_component_sub.start_date')->whereNull('payroll_component_sub.end_date');
                    })
                    ->orWhere(function ($query) {
                        $query->whereNull('payroll_component_sub.start_date')->whereNotNull('payroll_component_sub.end_date')
                            ->where('payroll_component_sub.end_date', '>=', DB::raw("payroll_component_sub.payroll_attendance_date"));
                    })
                    ->orWhere(function ($query) {
                        $query->whereNotNull('payroll_component_sub.start_date')->whereNull('payroll_component_sub.end_date')
                            ->where('payroll_component_sub.start_date', '<=', DB::raw("payroll_component_sub.payroll_attendance_date"));
                    })
                    ->orWhere(function ($query) {
                        $query->whereNotNull('payroll_component_sub.start_date')->whereNotNull('payroll_component_sub.end_date')
                            ->whereBetween('payroll_component_sub.payroll_attendance_date', [DB::raw("payroll_component_sub.start_date"), DB::raw("payroll_component_sub.end_date")]);
                    });
            });

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'payroll_component_sub.employee_number', 'direction' => 'ASC'],
            ['field' => 'payroll_component_sub.formulable_type', 'direction' => 'ASC'],
            ['field' => 'payroll_component_sub.payroll_componentable_morph_to_type', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'payroll_component_sub.payroll_componentable_morph_to_type', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function store($attributes)
    {
        $hydrated = $this->hydrateItem($attributes);
        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        return $this->model::query()->create($patchable);
    }

    public function update($identifier, $attributes)
    {
        $model = $this->model::query()->findOrfail($identifier);

        $hydrated = $this->hydrateItem($attributes);
        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        $model->update($patchable);

        return $model;
    }
}
