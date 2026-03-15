<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Concrete\LeaveService;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class LeaveRepositoryEloquent extends BaseRepositoryEloquent implements LeaveRepository
{
    public function __construct(
        protected readonly LeaveService $service
    ){
        parent::__construct(App::getInstance());
    }

    public function model(): string
    {
        return Leave::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'leaves.employee_id');
            })
            ->when(!empty($filters->leave_type_ids) && is_array($filters->leave_type_ids), function ($builder) use ($filters) {
                $builder->whereIn('leaves.leave_type_id', $filters->leave_type_ids);
            })
            ->when(!empty($filters->leave_ids) && is_array($filters->leave_ids), function ($builder) use ($filters) {
                $builder->whereIn('leaves.id', $filters->leave_ids);
            })
            ->when((
                (isset($filters->date_from) && Carbon::createFromFormat('Y-m-d', $filters->date_from)) &&
                (isset($filters->date_to) && Carbon::createFromFormat('Y-m-d', $filters->date_to))
            ),function($builder) use ($filters){
                $builder->whereBetween('leaves.date', [$filters->date_from, $filters->date_to]);
            })
            ->when(isset($filters->date_from_onwards) && Carbon::createFromFormat('Y-m-d', $filters->date_from_onwards), function($builder) use ($filters){
                $builder->where('leaves.date', '>=', $filters->date_from_onwards);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "employee_sub.number AS employee_number",
                "employee_sub.full_name AS employee_full_name",

                "leaves.id AS id",
                "leaves.ulid AS ulid",
                "leaves.employee_id AS employee_id",
                "leaves.leave_type_id AS leave_type_id",
                "leaves.date AS date",
            ]);

        return $queryBuilder;
    }

    public function paginate($filters, $relations = [], $orders = []): LengthAwarePaginator
    {
        $orders = empty($orders) ? [
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ['field' => 'leaves.date', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters, $relations = [], $orders = []): Collection
    {
        $orders = empty($orders) ? [
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ['field' => 'leaves.date', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function store($attributes): array
    {
        $employee = Employee::query()->findOrFail($attributes['employee_id']);
        $leaveType = LeaveType::query()->findOrFail($attributes['leave_type_id']);

        $createdCount = 0;

        $results = collect($attributes['dates'])->map(function($date) use ($employee, $leaveType, &$createdCount, $attributes) {

            $irregularity = '';
            $successful = false;
            $resultLabel = 'Not found';
            $resultType = 'default';

            $dateSeries = $this->service->getRunningBalanceByDate($employee, $leaveType, $date);
            $limitReached = $this->service->isLimitReached($employee, $leaveType, $date);

            $wholeDayLeaveMinimumBalanceToRequest = 1;

            $irregularity = match(true){
                !$dateSeries['eligible'] => 'Not eligible',
                (float)$dateSeries['running_balance'] < $wholeDayLeaveMinimumBalanceToRequest => 'Insufficient balance',
                $limitReached => 'Claim limit reached',
                default => $irregularity,
            };

            if(empty($irregularity)){

                if($this->model::query()->create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'date' => $date,
                ])){
                    $resultLabel = 'Leave created';
                    $resultType = 'success';
                    $successful = true;
                    $createdCount += 1;
                } else {
                    $resultLabel = 'Failed';
                    $resultType = 'danger';
                }

            } else {
                $resultLabel = $irregularity;
            }

            return [
                'date' => $date,
                'successful' => $successful,
                'result' => [
                    'label' => $resultLabel,
                    'type' => $resultType,
                ]
            ];
        });

        return [
            'results' => $results->values()->toArray(),
            'created' => $createdCount
        ];
    }
}
