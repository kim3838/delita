<?php

namespace App\Concrete;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\OvertimeRepository;
use App\Enums\RequestApprovalStatus;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Models\Attendance;
use App\Models\RequestApprovalState;
use App\Models\Shift;
use App\Traits\WorkPeriod;
use App\Transformers\AttendanceAdjustmentRequest\PatchableTransformer as AttendanceAdjustmentRequestPatchableTransformer;
use App\Transformers\OvertimeRequest\PatchableTransformer as OvertimeRequestPatchableTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class ApprovalService
{
    use WorkPeriod;

    public static array $seriesMap = [
        [
            'model_alias' => 'attendance_adjustment_request',
            'readable_name' => 'Attendance adjustment request',
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
            'foreign_path' => 'employee_foreign_relation_path',
            'employee_foreign_relation_path' => [
                [
                    'foreign' => 'employee_id',
                    'model' => 'employee'
                ],
            ]
        ],
    ];

    /**
     * @throws UnexpectedException
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

                return [empty($validationErrors), null];
            }

            if($action == RequestApprovalStatus::APPROVED){

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
                            }
                        }

                        break;
                }

                $this->chainRequestableAction(empty($validationErrors), $requestable, $approvalState , $requestablePatchable);
            }
        }

        return [empty($validationErrors), $validationErrors[0] ?? null];
    }

    public function chainRequestableAction($noValidationError, Model $requestable,RequestApprovalState $approvalState, $patchable): void
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
                App::make(OvertimeRepository::class)->store($patchable);
        }
    }
}
