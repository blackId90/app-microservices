<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class ContinentStoreOrUpdate extends AppBaseRequest {

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
            'continent_code' => ['required', 'string', 'max:2'],
            'continent_name' => ['required', 'string', 'max:30']
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes() {
        return [
            'continent_code' => trans('attributes.continent_code'),
            'continent_name' => trans('attributes.continent_name')
        ];
    }
}
