<?php

namespace App\Blueprint\Auth;

interface TwoFactorAuthenticationProvider
{
    /**
     * Generate a new secret key.
     *
     * @return string
     */
    public function generateSecretKey();

    /**
     * Get the two factor authentication QR code URL.
     *
     * @param  string  $appName
     * @param  string  $email
     * @param  string  $secret
     * @return string
     */
    public function qrCodeUrl($appName, $email, $secret);

    /**
     * Verify the given token.
     *
     * @param  string  $secret
     * @param  string  $code
     * @return bool
     */
    public function verify($secret, $code);
}
