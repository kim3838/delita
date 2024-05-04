<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TransformerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('Transformer', function($app, $attributes){
            return $this->transformerMap()[$attributes['type']][$attributes['module']];
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    protected function transformerMap(): array
    {
        return array(
            'selection' => array(
                'prototype' => \App\Transformers\Prototype\SelectionTransformer::class
            )
        );
    }
}
