<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class EmployeeRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeRepository
{
    public function model(): string
    {
        return Employee::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

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

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->first();
    }
}
