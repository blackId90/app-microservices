<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class LanguageStoreOrUpdate extends AppBaseRequest {

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
            'language_code' => ['required', 'string', 'lowercase', 'max:10'],
            'language_name' => ['required', 'string', 'max:100']
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes() {
        return [
            'language_code' => trans('attributes.language_code'),
            'language_name' => trans('attributes.language_name')
        ];
    }
}
