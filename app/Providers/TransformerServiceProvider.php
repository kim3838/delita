<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class TransformerServiceProvider extends ServiceProvider
{
    private const TRANSFORMER_MAP = [
        'selection' => [
            'prototype' => \App\Transformers\Prototype\SelectionTransformer::class,
            'json_preset' => \App\Transformers\JsonPreset\SelectionTransformer::class
        ],
        'basic' => [
            'account' => \App\Transformers\BasicTransformer::class,
            'company' => \App\Transformers\BasicTransformer::class,
            'user' => \App\Transformers\BasicTransformer::class,
            'employee' => \App\Transformers\BasicTransformer::class,
            'shift' => \App\Transformers\BasicTransformer::class,
            'formula' => \App\Transformers\BasicTransformer::class,
            'attendance' => \App\Transformers\BasicTransformer::class,
            'leave_type' => \App\Transformers\BasicTransformer::class,
        ]
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('Transformer', function ($app, array $attributes) {
            return $this->resolveTransformer($attributes['type'], $attributes['module']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    private function resolveTransformer(string $type, string $module): string
    {
        if (!isset(self::TRANSFORMER_MAP[$type])) {
            throw new InvalidArgumentException("Transformer type '{$type}' not found");
        }

        if (!isset(self::TRANSFORMER_MAP[$type][$module])) {
            throw new InvalidArgumentException("Transformer module '{$module}' not found for type '{$type}'");
        }

        return self::TRANSFORMER_MAP[$type][$module];
    }
}
