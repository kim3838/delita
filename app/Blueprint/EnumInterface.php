<?php

namespace App\Blueprint;

interface EnumInterface
{
    public function selection($enum): string;
}
