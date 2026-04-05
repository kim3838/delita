<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
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
            <p>Password Reset</p>

            <p>Hello {{$username}},</p>

            <p>You are receiving this email because we received a password reset request for your account.</p>

            <a href="{{$password_reset_url}}"><h3>Reset Password</h3></a>

            <p>Password reset link will expire in {{$expire_in_minutes}} minutes.</p>

            <p>If you’re having trouble clicking the "Reset Password", copy and paste the URL below into your web browser:</p>
            <p>{{$password_reset_url}}</p>
        </td>
    </tr>
</table>
</body>
</html>
