<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\Gender;
use App\Enums\IncomeTax;
use App\Enums\MaritalStatus;
use App\Enums\UserType;
use App\Facades\ResponseJson;
use Illuminate\Http\JsonResponse;

class EnumController extends Controller
{
    public function selection($enum): JsonResponse
    {
        $enum = match ($enum) {
            'account_type' => AccountType::class,
            'company_user_assignment_type' => CompanyUserAssignmentType::class,
            'compensation' => Compensation::class,
            'deduction' => Deduction::class,
            'income_tax' => IncomeTax::class,
            'gender' => Gender::class,
            'marital_status' => MaritalStatus::class,
            'user_type' => UserType::class,
            'formulable_type' => Formulable::class,
            default => throw new \InvalidArgumentException('Invalid enum type'),
        };

        return ResponseJson::successfulResponse([
            'data' => $enum::all()
        ]);
    }
}
