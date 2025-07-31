<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Account Verification</title>
</head>
<body style="font-family: sans-serif; background-color: #f9f9f9; padding: 30px;">
<table width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd; padding: 20px;">
    <tr>
        <td>
            <p>User Account Verification</p>

            <p>Hello <strong>{{$username}}</strong></p>

            <p>We are pleased to inform you that your email address <strong>{{ $email }}</strong> has been successfully registered in our system.</p>

            <h3>Login Credentials</h3>
            <ul style="line-height: 1.6;">
                <li><strong>Username:</strong> {{ $username }}</li>
                <li><strong>Email:</strong> {{ $email }}</li>
                <li><strong>Password:</strong> {{ $password }}</li>
                <li><strong>Login URL:</strong> <a href="{{ config('app.frontend_url') . '/login' }}">{{ config('app.frontend_url') . '/login' }}</a></li>
            </ul>

            <p>Please log in and change your password at your earliest convenience.</p>

            <p>Once again, welcome aboard! We’re excited to have you with us.</p>

            <p style="margin-top: 40px;">Warm regards<br>
        </td>
    </tr>
</table>
</body>
</html>
