<?php

namespace App\Http\Controllers;

use App\Traits\HasRequest;
use App\Traits\HasTotals;

abstract class Controller
{
    use HasRequest, HasTotals;
}
