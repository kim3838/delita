<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{$payroll_month_readable}} Payslip</title>
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
            <p>Hello <strong>{{$employee_full_name}}</strong></p>

            <p>Good day!</p>

            <p>We’re pleased to inform you that your payslip for <strong>{{$payroll_month_readable}}</strong>, sequence of <strong>{{$payroll_frequency}}</strong> has been successfully generated.</p>

            <p>Please find your payslip attached to this email for your reference.</p>

            <p>Your password is:</p>
            <ul style="line-height: 1.6;">
                <li>last name in lower case without space</li>
                <li>followed by employee number in lower case</li>
                <li>followed by your date of birth in this format: YYYYMMDD</li>
            </ul>

            <p>Example password: <strong>delacruzemp100019920101</strong></p>

            <p>Thank you for your continued hard work and dedication.</p>

            <p>Best regards,</p>
        </td>
    </tr>
</table>
</body>
</html>
