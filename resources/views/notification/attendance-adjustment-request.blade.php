<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{$payload->title}}</title>
    <style>
        * {
            font-family: sans-serif;
            font-size: 1.25rem;
        }
    </style>
</head>
<body style="background-color: #f9f9f9; padding: 30px;">
<table width="100%" style="max-width: 860px; margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd;">
    <tr>
        <td style="padding: 20px;">
            <p>{{$payload->title}} is awaiting your approval.</p>

            <div><strong>{{$payload->request_number}}</strong></div>
            <div>{{$payload->request_number_subtitle}}</div>
            <div>Requested by: <strong>{{$payload->requested_by_username}}</strong> {{$payload->date_requested_diff}}</div>
            <div>Attendance of: {{$payload->attendance_of}}</div>

            <p><strong>Schedule</strong></p>

            <div>
                <div>Work period: {{$payload->schedule_work_period}}</div>
                <div>Work start grace: {{$payload->shift_work_start_grace}}</div>
                @if($payload->shift_requires_lunch_out_and_in)
                    <div>Lunch period: {{$payload->schedule_lunch_period}}</div>
                    <div>Lunch start grace: {{$payload->shift_lunch_start_grace}}</div>
                @endif
                <div>Total duration: {{$payload->schedule_total_duration}}</div>
                <div>Overtime max duration: {{$payload->overtime_max_duration}}</div>
                <div>Holiday policy: {{$payload->holiday_policy}}</div>
            </div>

            <p><strong>Attendance</strong></p>

            <div>
                <div>First in: {{$payload->attendance_first_in}}</div>
                @if($payload->shift_requires_lunch_out_and_in)
                    <div>Lunch out: {{$payload->attendance_lunch_out}}</div>
                    <div>Lunch in: {{$payload->attendance_lunch_in}}</div>
                @endif
                <div>Last out: {{$payload->attendance_last_out}}</div>
            </div>

            <p><strong>Adjustment</strong></p>

            <div>
                <div>First in: {{$payload->adjustment_attendance_first_in}}</div>
                @if($payload->shift_requires_lunch_out_and_in)
                    <div>Lunch out: {{$payload->adjustment_attendance_lunch_out}}</div>
                    <div>Lunch in: {{$payload->adjustment_attendance_lunch_in}}</div>
                @endif
                <div>Last out: {{$payload->adjustment_attendance_last_out}}</div>
                <div>Remarks: {{$payload->remarks}}</div>
            </div>

            <p><strong>Login to apply your workflow: </strong> <a href="{{ config('app.frontend_url') . '/login' }}">{{ config('app.frontend_url') . '/login' }}</a></p>

            <p>Thank you for your continued hard work and dedication.</p>

            <p>Best regards,</p>
        </td>
    </tr>
</table>
</body>
</html>
