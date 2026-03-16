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
<body style="margin: 0; padding: 30px; background-color: #f9f9f9;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9f9f9;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border: 1px solid #dddddd;">
                <tr>
                    <td style="padding: 20px;">
                        <p style="margin-top: 0;">Confirm Your Email</p>

                        <p>You’ve received this message because your email address has been registered with our site.</p>

                        <p>Please click the button below to verify your email address and confirm that you are the owner of this account.</p>

                        <p>If you did not register with us, please disregard this email.</p>

                        <p style="margin: 24px 0;">
                            <a href="{{$verification_url}}" style="display: inline-block; padding: 12px 20px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                                Confirm Email
                            </a>
                        </p>

                        <p>Verification link will expire in {{$expire_in_minutes}} minutes.</p>

                        <p>Once confirmed, this email will be uniquely associated with your account.</p>

                        <p>If you’re having trouble clicking the "Confirm Email", copy and paste the URL below into your web browser:</p>
                        <p style="word-break: break-all; word-wrap: break-word; overflow-wrap: break-word;">
                            <a href="{{$verification_url}}" style="color: #2563eb; text-decoration: underline; word-break: break-all; word-wrap: break-word; overflow-wrap: break-word;">
                                {{$verification_url}}
                            </a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
