<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Account Credentials</title>
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
            <p>User Account Credentials</p>

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
        </td>
    </tr>
</table>
</body>
</html>
