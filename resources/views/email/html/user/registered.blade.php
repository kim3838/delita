<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Account Credentials</title>
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
            <p>User Account Credentials</p>

            <p>Your email address <strong>{{ $email }}</strong> has been successfully registered in {{config('app.name')}}.</p>

            <h3>Login Credentials</h3>
            <div>
                <div><strong>Username:</strong> {{ $username }}</div>
                <div><strong>Email:</strong> {{ $email }}</div>
                <div><strong>Password:</strong> {{ $password }}</div>
                <div><strong>Login URL:</strong> <a href="{{ config('app.frontend_url') . '/login' }}">{{ config('app.frontend_url') . '/login' }}</a></div>
            </div>

            <p>Please log in and change your password at your earliest convenience.</p>
        </td>
    </tr>
</table>
</body>
</html>
