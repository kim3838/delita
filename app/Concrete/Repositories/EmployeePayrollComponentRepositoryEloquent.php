<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Facades\Fractal;
use App\Models\EmployeePayrollComponent;
use App\Transformers\EmployeePayrollComponent\PatchableTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmployeePayrollComponentRepositoryEloquent extends BaseRepositoryEloquent implements EmployeePayrollComponentRepository
{
    public function model(): string
    {
        return EmployeePayrollComponent::class;
    }

    public function baseQueryBuilder($filters, $orders = null)
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::getQuery()
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
            ->select([
                "employee_sub.number AS employee_number",
                DB::raw("CONCAT(employee_payroll_components.payroll_componentable_id, '.', employee_payroll_components.payroll_componentable_type) AS payroll_componentable_morph"),
                DB::raw("
                    CASE
                        WHEN employee_payroll_components.payroll_componentable_type = 'compensation' THEN compensations.type
                        WHEN employee_payroll_components.payroll_componentable_type = 'deduction' THEN deductions.type
                    ELSE income_taxes.type
                    END AS payroll_componentable_morph_to_type
                "),
                "employee_payroll_components.*",
            ]);

        $queryBuilder = $this->queryAsSub($queryBuilder, 'payroll_component_sub')
            ->when(!empty($filters->payroll_componentable_morph_to_type) && is_array($filters->payroll_componentable_morph_to_type), function ($builder) use ($filters) {
                $builder->whereIn('payroll_component_sub.payroll_componentable_morph_to_type', $filters->payroll_componentable_morph_to_type);
            })
            ->when(!empty($filters->payroll_componentable_morph) && is_array($filters->payroll_componentable_morph), function ($builder) use ($filters) {
                $builder->whereIn('payroll_component_sub.payroll_componentable_morph', $filters->payroll_componentable_morph);
            })
            ->when(!empty($filters->formulable_types) && is_array($filters->formulable_types), function ($builder) use ($filters) {
                $builder->whereIn('payroll_component_sub.formulable_type', $filters->formulable_types);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "payroll_component_sub.*",
            ]);

        return $queryBuilder;
    }

    public function list($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'payroll_component_sub.employee_number', 'direction' => 'ASC'],
            ['field' => 'payroll_component_sub.formulable_type', 'direction' => 'ASC'],
            ['field' => 'payroll_component_sub.payroll_componentable_morph_to_type', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new $this->model());
    }

    public function store($attributes)
    {
        $hydrated = $this->hydrateItem($attributes);
        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        return $this->model::create($patchable);
    }

    public function update($id, $attributes)
    {
        $model = $this->model::findOrfail($id);

        $hydrated = $this->hydrateItem($attributes);
        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        $model->update($patchable);

        return $model;
    }
}
