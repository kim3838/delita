<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class Parsable implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $decoded = json_decode($value);
        $parsed = null;

        if(is_array($decoded)){
            $parsed = [];

            foreach ($decoded as $payload) {

                $parsedPayload = $this->parsePayload($payload);

                $parsed[] = $parsedPayload;
            }

            usort($parsed, function ($a, $b) {
                return $a->order <=> $b->order;
            });
        }

        if(is_object($decoded)){

            $parsed = $this->parsePayload($decoded);
        }

        return (object)[
            'cast' => $decoded,
            'parsed' => $parsed,
        ];
    }

    protected function parsePayload($payload): StdClass
    {
        $item = new StdClass();
        $item->key = $payload->key ?? null;
        $item->type = $payload->type ?? null;
        $item->label = $payload->label ?? null;
        $item->order = $payload->order ?? null;
        $item->readable = $payload->readable ?? null;

        $item->value = match($item->type) {
            'date' => $this->getDateFromPayload($payload->value ?? null),
            'array' => collect($payload->value ?? [])->map(fn ($p) => $this->parsePayload($p))->all(),
            default => $payload->value ?? null,
        };

        return $item;
    }

    public function getDateFromPayload($payload): string
    {
        $dateInstance = Carbon::parse($payload->base);

        //Parse year
        if(!is_null($payload->year) && is_numeric($payload->year)){

            $dateInstance->year($payload->year);
        }

        if(!is_null($payload->year) && is_string($payload->year)){

            [$method, $value] = array_pad(explode('.', $payload->year), 2, null);

            $value = empty($value) ? 1 : $value;

            $dateInstance->{$method}((int)$value);
        }

        //Parse month
        if(!is_null($payload->month) && is_numeric($payload->month)){
            $dateInstance->month($payload->month);
        }

        if(!is_null($payload->month) && is_string($payload->month)){

            [$method, $value] = array_pad(explode('.', $payload->month), 2, null);

            $value = empty($value) ? 1 : $value;

            $dateInstance->{$method}((int)$value);
        }

        //Parse day
        if(!is_null($payload->day) && is_numeric($payload->day)){

            $dateInstance->day($payload->day);
        }

        if(!is_null($payload->day) && is_string($payload->day)){

            [$method, $value] = array_pad(explode('.', $payload->day), 2, null);

            $value = empty($value) ? 1 : $value;

            $dateInstance->{$method}((int)$value);
        }

        //Parse time
        if(!is_null($payload->time) && is_array($payload->time)){

            $dateInstance->setTime(...$payload->time);
        }

        if(!is_null($payload->time) && is_string($payload->time)){

            [$method, $value] = array_pad(explode('.', $payload->time), 2, null);

            $dateInstance->{$method}();
        }

        return $dateInstance->toDateTimeString();
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return [
            $key => json_encode($value),
        ];
    }
}
