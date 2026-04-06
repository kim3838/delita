<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation</title>
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
            <p><strong>Confirm Your Email</strong></p>

            <p>You’ve received this message because your email address has been registered in {{config('app.name')}}.</p>

            <p style="margin: 24px 0;">
                <a href="{{$verification_url}}" style="display: inline-block; padding: 12px 20px; border:1px solid silver; text-decoration: none; border-radius: 4px; font-weight: bold;">
                    Confirm
                </a>
            </p>

            <p>Verification link will expire in {{$expire_in_minutes}} minutes.</p>
        </td>
    </tr>
</table>
</body>

</html>
