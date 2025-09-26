<?php

namespace App\Helpers;

class CookieHelper
{
    /**
     * Parse a cookie string into an associative array
     *
     * @param string|null $cookieString
     * @return array
     */
    public static function parseCookieString(?string $cookieString): array
    {
        if(empty($cookieString)){
            return [];
        }

        $cookieArray = [];
        $cookies = explode('; ', $cookieString);

        foreach ($cookies as $cookie) {
            if (str_contains($cookie, '=')) {
                [$name, $value] = explode('=', $cookie, 2);
                $cookieArray[trim($name)] = urldecode(trim($value));
            }
        }

        return $cookieArray;
    }
}
