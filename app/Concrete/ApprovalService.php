<?php

namespace App\Concrete;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Blueprint\Repositories\OvertimeRepository;
use App\Enums\PayrollStatus;
use App\Enums\RequestApprovalStatus;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\RequestApprovalState;
use App\Models\Shift;
use App\Traits\HasLeave;
use App\Transformers\AttendanceAdjustmentRequest\PatchableTransformer as AttendanceAdjustmentRequestPatchableTransformer;
use App\Transformers\LeaveRequest\PatchableTransformer as LeaveRequestPatchableTransformer;
use App\Transformers\OvertimeRequest\PatchableTransformer as OvertimeRequestPatchableTransformer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class ApprovalService
{
    public ?Company $company;

    use HasLeave;

    public static array $seriesMap = [
        [
            'model_alias' => 'attendance_adjustment_request',
            'readable_name' => 'Attendance adjustment request',
            'employable' => true,
            'foreign_path' => 'employee_foreign_relation_path',
            'employee_foreign_relation_path' => [
                [
                    'foreign' => 'attendance_id',
                    'model' => 'attendance'
                ], [
                    'foreign' => 'employee_id',
                    'model' => 'employee'
                ],
            ]
        ], [
            'model_alias' => 'overtime_request',
            'readable_name' => 'Overtime request',
            'employable' => true,
            'foreign_path' => 'employee_foreign_relation_path',
            'employee_foreign_relation_path' => [
                [
                    'foreign' => 'attendance_id',
                    'model' => 'attendance'
                ], [
                    'foreign' => 'employee_id',
                    'model' => 'employee'
                ],
            ]
        ], [
            'model_alias' => 'leave_request',
            'readable_name' => 'Leave request',
            'employable' => true,
            'foreign_path' => 'employee_foreign_relation_path',
            'employee_foreign_relation_path' => [
                [
                    'foreign' => 'employee_id',
                    'model' => 'employee'
                ],
            ]
        ], [
            'model_alias' => 'payroll_request',
            'readable_name' => 'Payroll',
            'employable' => false,
            'foreign_path' => '',
        ],
    ];

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    /**
     *
     * Respond with a validation error if there's any
     * otherwise, create requestable patchable
     * then use it on requestable final approval action
     *
     * @throws UnexpectedException
     * @throws BindingResolutionException
     */
    public function chainRequestableWorkflow(RequestApprovalStatus $action, RequestApprovalState $approvalState): array
    {
        $validationErrors = [];
        $requestable = $approvalState->requestable;
        $requestablePatchable = null;

        if(empty($requestable)){
            $validationErrors[] = 'Request not found';
        } else {

            if($action == RequestApprovalStatus::DECLINED){

                switch($approvalState->requestable_type){
                    case 'payroll_request':
                        $payroll = $requestable->payroll;

                        $payroll->update([
                            'status' => PayrollStatus::DRAFT->value,
                        ]);
                        break;
                }
                return [empty($validationErrors), null];
            }

            if($action == RequestApprovalStatus::APPROVED){

                $payrollService = app(PayrollServiceInterface::class, [$this->company]);

                switch($approvalState->requestable_type){
                    case 'attendance_adjustment_request':
                    case 'overtime_request':

                        $requestablePatchableTransformer = match($approvalState->requestable_type){
                            'attendance_adjustment_request' => AttendanceAdjustmentRequestPatchableTransformer::class,
                            'overtime_request' => OvertimeRequestPatchableTransformer::class,
                        };

                        $requestablePatchable = Fractal::item($requestable, $requestablePatchableTransformer);

                        //Attendance
                        $attendance = $requestable->attendance;

                        if (empty($attendance) || !$attendance instanceof Attendance) {
                            $validationErrors[] = 'Attendance not found';

                        } else {

                            $attendanceDate = Carbon::parse($attendance->date);

                            $shift = Shift::query()->find($attendance->shift_id);

                            if(empty($shift) || !$shift instanceof Shift){
                                $validationErrors[] = 'Shift not found';
                            } else {

                                $this->setShift($shift);

                                /**
                                 * After setting up shift,
                                 * Get the shift work day by attendance date
                                 **/
                                $this->setAttendanceSchedule($attendanceDate);

                                /**
                                 * Validate attendance shift details if still match the current shift and schedule settings
                                 * */
                                list(
                                    $currentShiftAndAttendanceShiftStillTheSame,
                                    $currentShiftScheduleAndAttendanceShiftScheduleStillTheSame
                                ) = $this->validateAttendanceShiftDetails(
                                    $this->shift,
                                    $this->attendanceSchedule,
                                    $attendance->shiftDetail->toArray(),
                                    $attendance->shiftDetail->toArray()
                                );

                                if(!$currentShiftAndAttendanceShiftStillTheSame){

                                    $validationErrors[] = 'Unable to proceed, shift settings have changed';
                                }

                                if(!$currentShiftScheduleAndAttendanceShiftScheduleStillTheSame){

                                    $validationErrors[] = 'Unable to proceed, shift schedule settings have changed';
                                }

                                $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($attendance->employee, $attendance->date);

                                if ($isDateOnAnyPayrollStatementAttendance) {

                                    $validationErrors[] = 'Unable to proceed, payroll generated.';
                                }
                            }
                        }

                        break;
                    case 'leave_request':
                        $requestablePatchable = Fractal::item($requestable, LeaveRequestPatchableTransformer::class);

                        $datePeriod = CarbonPeriod::create($requestable->date_from, $requestable->date_to);

                        foreach($datePeriod as $date){

                            $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($requestable->employee, $date);

                            if($isDateOnAnyPayrollStatementAttendance){
                                $validationErrors[] = 'Unable to proceed ' .$date->toDateString() . ', payroll generated.';

                                break;
                            }
                        }

                        break;
                }

                $this->chainRequestableAction(empty($validationErrors), $requestable, $approvalState , $requestablePatchable);
            }
        }

        return [empty($validationErrors), $validationErrors[0] ?? null];
    }

    /**
     * @throws UnexpectedException|BindingResolutionException
     */
    public function chainRequestableAction($noValidationError, Model $requestable, RequestApprovalState $approvalState, $patchable): void
    {
        if(!$noValidationError) return;

        $requestableLastApprovalState = $requestable->approvalStates->sortByDesc('order')->first();

        $approvalStateIsTheLastRequestableApprovalWorkflow = ($requestableLastApprovalState->id == $approvalState->id) &&
            ($requestableLastApprovalState->requestable_type == $approvalState->requestable_type);

        if(!$approvalStateIsTheLastRequestableApprovalWorkflow) return;

        switch($approvalState->requestable_type){

            case 'attendance_adjustment_request':
                App::make(AttendanceRepository::class)->update($requestable->attendance->ulid, $patchable);
                break;
            case 'overtime_request':
                $overtimeRepository = App::make(OvertimeRepository::class);

                $overtime = $overtimeRepository->model()::query()
                    ->where('attendance_id', $patchable['attendance_id'])
                    ->first();

                if(empty($overtime)){
                    $overtimeRepository->store($patchable);
                } else {
                    $overtimeRepository->update($overtime->ulid, $patchable);
                }
                break;
            case 'leave_request':

                $leaveDatePeriod = CarbonPeriod::create($patchable['date_from'], $patchable['date_to']);

                $filteredDates = $this->filterLeaveDateRange(
                    $patchable['company_id'],
                    $patchable['employee_id'],
                    $patchable['shift_id'],
                    $leaveDatePeriod
                );

                $leaveStoreResults = App::make(LeaveRepository::class)->store([
                    'employee_id' => $patchable['employee_id'],
                    'leave_type_id' => $patchable['leave_type_id'],
                    'dates' => $filteredDates,
                ]);

                $results = $leaveStoreResults['results'];

                $mappedResults = array_map(function($result){
                    return [
                        'date' => $result['date'],
                        'successful' => $result['successful'],
                        'remarks'=> $result['result']['label'] ?? 'Remarks not provided.',
                    ];
                }, $results);

                $requestable->results()->createMany($mappedResults);
                break;
            case 'payroll_request':

                $payroll = $requestable->payroll;

                $payroll->update([
                    'status' => PayrollStatus::COMPLETE->value,
                ]);

        }
    }
}
