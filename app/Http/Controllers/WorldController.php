<?php

namespace App\Http\Controllers;

use App\Facades\ResponseJson;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\Timezone;

class WorldController extends Controller
{
    public function countrySelection()
    {
        $collection = Country::orderBy('name')->get();

        $formatted = $collection->map(function($item){
            return [
                'text' => $item->name,
                'value' => $item->id
            ];
        });

        return ResponseJson::successfulResponse([
            'selection' => $formatted
        ]);
    }

    public function currencySelection()
    {
        $collection = Currency::select('code', 'name')->distinct()->orderBy('code')->get();

        $formatted = $collection->map(function($item){
            return [
                'text' => $item->code . ' - ' . $item->name,
                'value' => $item->code
            ];
        });

        return ResponseJson::successfulResponse([
            'selection' => $formatted
        ]);
    }

    public function timezoneSelection()
    {
        $collection = Timezone::select('name')->distinct()->orderBy('name')->get();

        $formatted = $collection->map(function($item){
            return [
                'text' => $item->name,
                'value' => $item->name
            ];
        });

        return ResponseJson::successfulResponse([
            'selection' => $formatted
        ]);
    }
}
