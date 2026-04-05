<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll draft #: {{$payroll->number}}</title>
    <style>
        * {
            font-family: sans-serif;
        }
    </style>
</head>
<body style="background-color: #f9f9f9; padding: 30px;">
<table width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd;">
    <tr>
        <td style="padding: 20px;">
            <p>Hello <strong>{{$user->name}}</strong></p>

            <p>Your payroll draft #: <strong>{{$payroll->number}}</strong> has been successfully generated.</p>

            <ul style="line-height: 1.6;">
                <li>Month: {{$payroll->month_readable}}</li>
                <li>Frequency: {{\Illuminate\Support\Arr::get($payroll->pay_frequency, 'text')}}</li>
                <li>Sequence: {{\Illuminate\Support\Arr::get($payroll->frequency_sequence, 'text')}}</li>
                <li>Period: {{$payroll->date_range_readable}}</li>
                <li>Remarks: {{$payroll->remarks}}</li>
            </ul>

            <p>Thank you for your continued hard work and dedication.</p>

            <p>Best regards,</p>
        </td>
    </tr>
</table>
</body>
</html>
