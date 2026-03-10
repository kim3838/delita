<?php

namespace App\Traits;

use Illuminate\Support\Facades\Request;

trait HasRequest
{
    public function resolveFilterValueFromRequestFacadeIfNotSet($filters, $filterSlug): void
    {
        if(!isset($filters->{$filterSlug}) && Request::exists($filterSlug)){

            $filters->{$filterSlug} = Request::get($filterSlug);
        }
    }
}
