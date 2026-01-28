<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation</title>
    <style>
        * {
            font-family: sans-serif;
        }
    </style>
</head>
<body style="background-color: #f9f9f9; padding: 30px;">
<table width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd; padding: 20px;">
    <tr>
        <td>
            <p>Confirm Your Email</p>

            <p>You’ve received this message because your email address has been registered with our site. Please click the button below to verify your email address and confirm that you are the owner of this account.</p>

            <p>If you did not register with us, please disregard this email.</p>

            <a href="{{$verification_url}}"><h3>Confirm Email</h3></a>

            <p>Verification link will expire in {{$expire_in_minutes}} minutes.</p>

            <p>Once confirmed, this email will be uniquely associated with your account.</p>

            <p>If you’re having trouble clicking the "Confirm Email", copy and paste the URL below into your web browser:</p>
            <p>{{$verification_url}}</p>
        </td>
    </tr>
</table>
</body>
</html>
