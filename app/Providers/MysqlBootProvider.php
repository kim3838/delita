<?php

namespace App\Providers;

use DB;
use Illuminate\Support\ServiceProvider;

class MysqlBootProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $recursionDepth = 8000;

        $override = true;

        if($override){

            DB::statement("SET SESSION cte_max_recursion_depth = $recursionDepth;");
        }

    }
}
