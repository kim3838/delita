<?php

namespace App\Http\Controllers;

use App\Facades\ResponseJson;
use Illuminate\Http\Request;

class JsonController
{
    public function read(Request $request)
    {
        if($request->expectsJson()){

            $request->validate([
                'json_file' => 'required|file|mimes:json|mimetypes:application/json,text/json',
            ]);

            $json = $request->file('json_file');

            $jsonData = json_decode($json->get());

            return ResponseJson::successfulResponse([
                'json_content' => $jsonData
            ]);
        }

        abort(404);
    }
}
