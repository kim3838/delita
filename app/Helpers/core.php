<?php

if (!function_exists('_now_timestamp')) {
    /**
     * Get timestamp
     *
     * @return false|int
     */
    function _now_timestamp(): false|int
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
    function _str_random(int $length): string
    {
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

function _log_query_builder_with_bindings(\Illuminate\Database\Query\Builder $queryBuilder, $name = 'query')
{
    $bindings = $queryBuilder->getBindings();

    $fullSql = null;

    if(count($bindings)){
        $fullSql = vsprintf(
            str_replace('?', "'%s'", $queryBuilder->toSql()),
            $bindings
        );
    }

    $log = $fullSql ?: $queryBuilder->toSql();

    \Illuminate\Support\Facades\Storage::disk('local')->put("$name.sql", $log);
}

if(!function_exists('_clear_debug')){
    function _clear_debug(): void
    {
        $logFile = config('logging.channels.debug.path');

        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
    }
}

if(!function_exists('_debug')){
    function _debug($value): void
    {
        \Illuminate\Support\Facades\Log::channel('debug')->debug($value);
    }
}

if (!function_exists('convertEnumValueToArray')) {
    /**
     * Convert a numeric enum value to its array representation
     *
     * @param string $enumClass The enum class name
     * @param int|null $numericValue The numeric value to convert
     * @return array|null The enum's array representation or null if invalid
     */
    function convertEnumValueToArray(string $enumClass, ?int $numericValue): ?array
    {
        return is_numeric($numericValue)
            ? $enumClass::tryFrom($numericValue)?->toArray()
            : null;
    }
}

if (!function_exists('isNameInEnum')){
    /**
     * Check if the name is one of the cases
     *
     * @param $enumClass
     * @param $name
     * @return bool
     */
    function isNameInEnum($enumClass, $name): bool
    {
        return in_array($name, array_map(fn($c) => $c->name, $enumClass::cases()), true);
    }

}
