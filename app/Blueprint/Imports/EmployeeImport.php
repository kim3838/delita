<?php

namespace App\Blueprint\Imports;

use App\Blueprint\ImportInterface;

interface EmployeeImport extends ImportInterface
{
    function getExistingEmployeeNumbers($companyId): array;
}
