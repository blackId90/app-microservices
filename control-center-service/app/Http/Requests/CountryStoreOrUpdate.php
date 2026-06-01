<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class CountryStoreOrUpdate extends AppBaseRequest {
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
        return [
            'country_code' => ['required', 'string', 'uppercase', 'size:2'],
            'country_alpha_3' => ['required', 'string', 'uppercase', 'size:3'],
            'country_name' => ['required', 'string', 'max:80'],
            'country_capital' => ['required', 'string', 'max:80'],
            'country_phone' => ['required', 'integer', 'min:1', 'max:999999'],
            'country_continent_code' => ['required', 'string', 'uppercase', 'size:2'],
            'country_currency_code' => ['required', 'string', 'uppercase', 'size:3']
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes() {
        return [
            'country_code' => trans('attributes.country_code'),
            'country_alpha_3' => trans('attributes.country_alpha_3'),
            'country_name' => trans('attributes.country_name'),
            'country_capital' => trans('attributes.country_capital'),
            'country_phone' => trans('attributes.country_phone'),
            'country_continent_code' => trans('attributes.country_continent_code'),
            'country_currency_code' => trans('attributes.country_currency_code')
        ];
    }
}
