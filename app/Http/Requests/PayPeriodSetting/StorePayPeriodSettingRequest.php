<?php

namespace App\Http\Requests\PayPeriodSetting;

use App\Models\PayPeriodSetting;
use Illuminate\Foundation\Http\FormRequest;

class StorePayPeriodSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PayPeriodSetting::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'days_to_pay_after_cut_off' => 'required|numeric',
            'time_period_preset_reference' => 'required',
            'monthly_pay_period' => 'required',
            'semimonthly_pay_period' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'days_to_pay_after_cut_off.required' => 'Days to pay after cut-off is required',
            'days_to_pay_after_cut_off.numeric' => 'Days to pay after cut-off must be numeric',
            'time_period_preset_reference.required' => 'Pay period preset is required',
            'monthly_pay_period.required' => 'Monthly pay period is required',
            'semimonthly_pay_period.required' => 'Semimonthly pay period is required',
        ];
    }
}
