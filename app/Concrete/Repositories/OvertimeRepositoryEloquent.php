<?php

namespace App\Concrete\Repositories;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\OvertimeRepository;
use App\Concrete\AttendanceSplitter;
use App\Concrete\BaseRepositoryEloquent;
use App\Exceptions\UnexpectedException;
use App\Models\Company;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class OvertimeRepositoryEloquent extends BaseRepositoryEloquent implements OvertimeRepository
{
    public function model(): string
    {
        return Overtime::class;
    }

    public function baseQueryBuilder($filters, $orders = [])
    {
        $attendanceRepositoryFilter = clone $filters;

        $attendanceQueryBuilder = App::make(AttendanceRepository::class)->baseQueryBuilder($attendanceRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($attendanceQueryBuilder, 'attendance_sub', function ($join) {
                $join->on('attendance_sub.id', '=', 'overtimes.attendance_id');
            })
            ->when(!empty($filters->overtime_ids) && is_array($filters->overtime_ids), function ($builder) use ($filters) {
                $builder->whereIn('overtimes.id', $filters->overtime_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                "overtimes.id AS id",
                "overtimes.ulid AS ulid",
                "overtimes.attendance_id AS attendance_id",
                "overtimes.start AS start",
                "overtimes.end AS end",
                "overtimes.duration AS duration",

                /**
                 * Attendance
                 **/
                "attendance_sub.ulid AS attendance_ulid",
                "attendance_sub.employee_id AS attendance_employee_id",
                "attendance_sub.shift_id AS attendance_shift_id",
                "attendance_sub.date AS attendance_date",

                /**
                 * Shift
                 **/
                "attendance_sub.shift_max_overtime AS attendance_shift_max_overtime",

                /**
                 * Shift Schedule
                 **/
                "attendance_sub.shift_schedule_week_day AS attendance_shift_schedule_week_day",
                "attendance_sub.shift_schedule_is_flexible AS attendance_shift_schedule_is_flexible",
                "attendance_sub.shift_schedule_timezone AS attendance_shift_schedule_timezone",
                "attendance_sub.shift_schedule_work_start AS attendance_shift_schedule_work_start",
                "attendance_sub.shift_schedule_work_end AS attendance_shift_schedule_work_end",
                "attendance_sub.shift_schedule_total_work_hours_with_breaks AS attendance_shift_schedule_total_work_hours_with_breaks",
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function paginate($filters, $relations = [], $orders = []): LengthAwarePaginator
    {
        $orders = empty($orders) ? [
            ['field' => 'attendance_sub.employee_number', 'direction' => 'ASC'],
            ['field' => 'date', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'attendance_sub.date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    /**
     * @throws UnexpectedException
     */
    public function store($attributes, ?AttendanceSplitter $splitterInterface = null)
    {
        $attendanceSplitter = $splitterInterface
            ?: app(AttendanceSplitterInterface::class, [Company::query()->find($attributes['company_id'])]);

        $overtime =  $this->model::query()->create([
            ...$attributes,
            'duration' => abs(Carbon::parse($attributes['end'])->diffInMinutes(Carbon::parse($attributes['start'])))
        ]);

        $attendanceSplitter->generate($overtime->attendance);

        return $overtime;
    }

    /**
     * @throws UnexpectedException
     */
    public function update($identifier, $attributes, ?AttendanceSplitter $splitterInterface = null)
    {
        $attendanceSplitter = $splitterInterface
            ?: app(AttendanceSplitterInterface::class, [Company::query()->find($attributes['company_id'])]);

        $overtime = $this->model::query()->where('ulid', $identifier)->firstOrFail();

        $update = collect($attributes)->except(['id', 'ulid', 'company_id', 'date', 'attendance_id'])->toArray();

        $overtime->update([
            ...$update,
            'duration' => abs(Carbon::parse($attributes['end'])->diffInMinutes(Carbon::parse($attributes['start'])))
        ]);

        $attendanceSplitter->generate($overtime->attendance);

        return $overtime;
    }

    /**
     * @throws UnexpectedException
     */
    public function batchDelete($ids, ?AttendanceSplitter $splitterInterface = null): int
    {
        $attendanceSplitter = $splitterInterface
            ?: app(AttendanceSplitterInterface::class, [Company::query()->find(request()->input('company_id'))]);

        foreach ($ids as $id) {

            $overtime = $this->model::query()->findOrFail($id);
            $attendance = clone $overtime->attendance;

            /**
             * Delete overtime
             **/
            $overtime->delete();

            /**
             * Rebuild attendance details
             **/
            $attendanceSplitter->generate($attendance);
        }

        return true;
    }
}
