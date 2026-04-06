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
            <div>Payroll #: <strong>{{$payload->payroll_number}}</strong></div>

            <p><strong>Payroll</strong></p>

            <div>
                <div>Remarks: {{$payload->payroll_remarks}}</div>
                <div>Month: {{$payload->payroll_month}}</div>
                <div>Sequence: {{$payload->payroll_sequence}}</div>
                <div>Period: {{$payload->payroll_period}}</div>
                <div>Total employer contr. share: <strong>{{$payload->payroll_total_employer_contribution_share}}</strong></div>
                <div>Total tax withheld: <strong>{{$payload->payroll_total_tax_withheld}}</strong></div>
                <div>Total tax refund: <strong>{{$payload->payroll_total_tax_refund}}</strong></div>
                <div>Total net due: <strong>{{$payload->payroll_total_net_due}}</strong></div>
            </div>

            <p><strong>Login to apply your workflow: </strong> <a href="{{ config('app.frontend_url') . '/login' }}">{{ config('app.frontend_url') . '/login' }}</a></p>

            <p>Thank you for your continued hard work and dedication.</p>

            <p>Best regards,</p>
        </td>
    </tr>
</table>
</body>
</html>
