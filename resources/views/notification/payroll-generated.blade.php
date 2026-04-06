<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll draft #: {{$payroll->number}}</title>
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

            <p>Your payroll draft #: <strong>{{$payroll->number}}</strong> has been successfully generated.</p>

            <div>
                <div>Month: {{$payroll->month_readable}}</div>
                <div>Frequency: {{\Illuminate\Support\Arr::get($payroll->pay_frequency, 'text')}}</div>
                <div>Sequence: {{\Illuminate\Support\Arr::get($payroll->frequency_sequence, 'text')}}</div>
                <div>Period: {{$payroll->date_range_readable}}</div>
                <div>Remarks: {{$payroll->remarks}}</div>
            </div>

            <p>Thank you for your continued hard work and dedication.</p>

            <p>Best regards,</p>
        </td>
    </tr>
</table>
</body>
</html>
