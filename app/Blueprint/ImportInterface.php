<?php

namespace App\Blueprint;

use Illuminate\Http\Request;

interface ImportInterface
{
    public function exportTemplate(): string;

    public function validateData($data, $companyId): array;

    public function read(Request $request): array;

    public function reValidate(Request $request): array;

    public function save(Request $request): array;

    function resolvedData($data, $companyId): array;
}
