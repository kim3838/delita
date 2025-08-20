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

if(!function_exists('_is_instance_of_any')){
    function _is_instance_of_any($object, array $classes): bool {
        return array_any($classes, fn($class) => $object instanceof $class);
    }
}

function _log_query_builder_with_bindings(\Illuminate\Database\Query\Builder $queryBuilder, $logKey = 'Sql w/ bindings')
{
    $bindings = $queryBuilder->getBindings();
    $escapedBindings = array_map(function ($binding) {
        return is_numeric($binding) ? $binding : addslashes($binding);
    }, $bindings);
    $fullSql = vsprintf(
        str_replace('?', "'%s'", $queryBuilder->toSql()),
        $escapedBindings
    );

    \Illuminate\Support\Facades\Log::info([
        $logKey => $fullSql,
    ]);
}

if(!function_exists('_debug')){
    function _debug($value): void
    {
        \Illuminate\Support\Facades\Log::debug($value);
    }
}

if (!function_exists('read_json')) {
    /**
     * Read and decode JSON file from resources/json
     *
     * @param string $filename
     * @return array
     */
    function read_json(string $filename): array
    {
        $path = resource_path("json/{$filename}");

        if (!file_exists($path)) {
            throw new \InvalidArgumentException("JSON file not found: {$filename}");
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }
}




