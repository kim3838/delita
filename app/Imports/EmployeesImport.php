<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation
{
    use Importable, RemembersRowNumber;

    public function __construct(
        protected $companyId
    ){}

    /**
     * @param array $row
     *
     * @return Model|Employee|null
     */
    public function model(array $row): Model|Employee|null
    {
        return new Employee([
            'company_id' => $this->companyId,
            'number' => $row['number'],
            'family_name' => $row['family_name'],
            'given_name' => $row['given_name'],
        ]);
    }

    public function rules(): array
    {
        return [
            'number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees')->where(function ($query) {
                    return $query->where('company_id', $this->companyId);
                })
            ],
            'family_name' => 'required|string|max:255',
            'given_name' => 'required|string|max:255',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'number.required' => 'Employee number is required.',
            'number.unique' => 'Employee number already in use.',
            'family_name.required' => 'Family name is required.',
            'given_name.required' => 'Given name is required.',
        ];
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
