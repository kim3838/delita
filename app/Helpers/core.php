<?php

if (!function_exists('_now_timestamp')) {
    /**
     * Get timestamp
     *
     * @return string
     */
    function _now_timestamp()
    {
        return strtotime(date("Y-m-d H:i:s"));
    }
}


if (!function_exists('_str_random')) {
    /**
     * Generate random string
     *
     * @return string
     */
    function _str_random($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';

        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

