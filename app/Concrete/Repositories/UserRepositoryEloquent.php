<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\UserRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepositoryEloquent extends BaseRepositoryEloquent implements UserRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->first();
    }

    public function autoGenerate($data)
    {
        $companyId = $data['company_id'];
        $officeEmail = $data['office_email'];
        $familyName = preg_replace('/\s+/', '', $data['family_name']);
        $givenName = preg_replace('/\s+/', '', $data['given_name']);

        $userCount = (int)User::count();
        $companyTimezone = Company::find($companyId)->timezone;

        $familyName = strtolower($familyName);
        $givenNameFirstCharacter = substr(strtolower($givenName), 0, 1);
        $username = "$familyName.$givenNameFirstCharacter." . ($userCount+1);

        return $this->store([
            'name' => $username,
            'email' => $officeEmail,
            'password' => Hash::make(Str::random(8)),
            'status' => UserStatus::ACTIVE->value,
            'timezone' => $companyTimezone,
        ]);
    }
}
