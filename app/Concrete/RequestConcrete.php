<?php

namespace App\Concrete;

use App\Blueprint\RequestInterface;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class RequestConcrete implements RequestInterface
{
    public ?int $accountId = null;
    public ?int $companyId = null;

    public object $payload;
    public object $filters;

    public function __construct()
    {
        $this->payload = (object) [];
        $this->filters = (object) [];
    }

    public function debug($caller): void
    {
        _debug([
            $caller . ' RequestConcrete all' => [
                'accountId' => $this->accountId,
                'companyId' => $this->companyId,
                'payload' => $this->payload,
                'filters' => $this->filters,
            ],
        ]);
    }
}
