<?php

namespace App\Http\Controllers;

use App\Blueprint\EnumInterface;
use App\Facades\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class EnumController extends Controller
{
    public function selection($enum): JsonResponse
    {
        $enum = App::make(EnumInterface::class)->selection($enum);

        return ResponseJson::successfulResponse([
            'data' => $enum::all()
        ]);
    }
}
