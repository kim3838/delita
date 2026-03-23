<?php

namespace App\Http\Requests\Account;

use App\Concrete\ContactConcrete;
use Illuminate\Foundation\Http\FormRequest;

class BaseAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                function ($attribute, $value, $fail) {

                    $contactService = new ContactConcrete();

                    $exceptAccountId = $this->route('accountId');

                    if ($contactService->isEmailTakenAsAccountEmail($value, $exceptAccountId)) {

                        $fail('Email has already been taken');
                    }
                },
            ],
            'spliced_subscriptions' => ['array'],
            'subscriptions' => ['array'],
            'subscriptions.*.id' => ['nullable', 'integer'],
            'subscriptions.*.account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'subscriptions.*.module' => [
                function($attribute, $value, $fail){

                    $index = $this->getSubscriptionIndex($attribute);
                    $module = $this->input("subscriptions.{$index}");

                    if(!is_numeric($module['module'])){
                        $fail('Subscriptions: Module is invalid');
                    } else {

                        $modules = collect($this->input('subscriptions'))
                            ->pluck('module')
                            ->filter(fn ($v) => is_numeric($v))
                            ->values();

                        // Count occurrences of the current module
                        if ($modules->filter(fn ($v) => $v == $value)->count() > 1) {
                            $fail('Subscriptions: Duplicate modules');
                        }
                    }
                },
            ],
            'subscriptions.*.plan' => [
                function($attribute, $value, $fail){

                    $index = $this->getSubscriptionIndex($attribute);
                    $module = $this->input("subscriptions.{$index}");

                    if(!is_numeric($module['plan'])){
                        $fail('Subscriptions: Plan is invalid');
                    }
                }
            ],
        ];
    }

    private function getSubscriptionIndex($attribute): int
    {
        preg_match('/subscriptions\.(\d+)\./', $attribute, $matches);
        return (int)$matches[1];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',

            'spliced_subscriptions.array' => 'Spliced subscriptions: must be an array',
            'subscriptions.array' => 'Subscriptions: must be an array',
            'subscriptions.*.account_id.exists' => 'Subscriptions: Account does not exist',
        ];
    }


}
