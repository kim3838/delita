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

            <p><strong>Leave</strong></p>

            <ul style="line-height: 1.2;">
                <li>Employee: {{$payload->employee}}</li>
                <li>Leave type: {{$payload->leave_type}}</li>
                <li>Leave date: {{$payload->leave_date_range}}</li>
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
