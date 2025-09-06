<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeRepository
{
    public function model(): string
    {
        return Employee::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("employees.company_id"), $value);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER() AS 'row_number'"),
                "employees.*"
            ])
            ->orderBy("employees.family_name", 'ASC')
            ->orderBy("employees.given_name", 'ASC');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new $this->model);
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("employees.company_id"), $value);
            })
            ->when(!empty($filters->id) && is_array($filters->id), function ($builder) use ($filters) {
                $builder->whereIn('employees.id', $filters->id);
            })
            ->when(!empty($filters->except_id) && is_array($filters->except_id), function ($builder) use ($filters) {
                $builder->whereNotIn('employees.id', $filters->except_id);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->whereRaw("CONCAT_WS(' ', family_name, middle_name, given_name) LIKE ?", ["%{$value}%"]);
            })
            ->select([
                "employees.*"
            ])
            ->orderBy("employees.family_name", 'ASC')
            ->orderBy("employees.middle_name", 'ASC')
            ->orderBy("employees.given_name", 'ASC');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new $this->model);
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->firstOrFail();
    }
}
