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

            <ul style="line-height: 1.2;">
                <li>Work period: {{$payload->schedule_work_period}}</li>
                <li>Total duration: {{$payload->schedule_total_duration}}</li>
                <li>Overtime max duration: {{$payload->overtime_max_duration}}</li>
                <li>Holiday policy: {{$payload->holiday_policy}}</li>
            </ul>

            <p><strong>Attendance</strong></p>

            <ul style="line-height: 1.2;">
                <li>Last out: {{$payload->attendance_last_out}}</li>
            </ul>

            <p><strong>Overtime</strong></p>

            <ul style="line-height: 1.2;">
                <li>Start: {{$payload->overtime_start}}</li>
                <li>End: {{$payload->overtime_end}}</li>
                <li>Total duration: {{$payload->total_duration}}</li>
                <li>Remarks: {{$payload->remarks}}</li>
            </ul>

            <p><strong>Login to apply your workflow: </strong> <a href="{{ config('app.frontend_url') . '/login' }}">{{ config('app.frontend_url') . '/login' }}</a></p>

            <p>Thank you for your continued hard work and dedication.</p>

            <p>Best regards,</p>
        </td>
    </tr>
</table>
</body>
</html>
