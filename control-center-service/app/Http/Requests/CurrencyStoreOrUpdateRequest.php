<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CurrencyStoreOrUpdateRequest extends AppBaseRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        $currencyId = $this->route('currencyId');

        return [
            'currency_code' => ['required', 'string', 'max:3'],
            'currency_name' => ['required', 'string', 'max:50'],
            'currency_symbol' => ['required', 'max:50'],
            'currency_is_active' => [Rule::requiredIf($currencyId !== null), 'boolean:strict'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes() {
        return [
            'currency_code' => trans('attributes.currency_code'),
            'currency_name' => trans('attributes.currency_name'),
            'currency_symbol' => trans('attributes.currency_symbol'),
            'currency_is_active' => trans('attributes.currency_is_active')
        ];
    }
}
