<?php

namespace App\Blueprint;

/**
 * @property int $accountId
 * @property int $companyId
 * @property object $payload
 * @property object $filters
 **/
interface RequestInterface
{
    public function debug($caller): void;
}
