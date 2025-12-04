<?php

namespace App\Http\Requests\IncomeTax;

use App\Models\IncomeTax;
use Illuminate\Foundation\Http\FormRequest;

class DestroyIncomeTaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $incomeTax = IncomeTax::query()->findOrfail($this->route('incomeTaxId'));

        return $this->user()->can('delete', $incomeTax);
    }
}
