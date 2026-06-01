<?php

namespace App\Http\Requests;

use App\Enums\UserGenderEnum;
use Illuminate\Validation\Rules\Enum;

class InternalRegisterRequest extends AppBaseRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            //* Company validation
            'company.company_name' => ['required', 'string', 'max:150'],
            'company.company_address' => ['required', 'string', 'max:255'],
            'company.company_village_id' => ['required', 'integer:strict', 'exists:reg_villages,village_id'],
            'company.company_zip_code' => ['nullable', 'string', 'max:10'],
            'company.company_phone' => ['required', 'string', 'unique:companies,company_phone', 'max:15'],
            'company.company_fax' => ['nullable', 'string', 'unique:companies,company_fax', 'max:15'],
            'company.company_website' => ['nullable', 'string', 'max:100'],
            'company.company_email' => ['required', 'email', 'unique:companies,company_email', 'max:100'],

            //* User validation
            'user.user_auth_user_id' => ['required', 'uuid'],
            'user.user_first_name' => ['required', 'string', 'max:100'],
            'user.user_last_name' => ['required', 'string', 'max:100'],
            'user.user_gender' => ['required', new Enum(UserGenderEnum::class)],
            'user.user_address' => ['nullable', 'string', 'max:255'],
            'user.user_village_id' => ['nullable', 'integer:strict', 'exists:reg_villages,village_id'],
            'user.user_zip_code' => ['nullable', 'string', 'max:10'],
            'user.user_phone' => ['required', 'string', 'unique:users,user_phone', 'max:15'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            //* Company attributes
            'company.company_name' => trans('attributes.company.company_name'),
            'company.company_address' => trans('attributes.company.company_address'),
            'company.company_village_id' => trans('attributes.company.company_village_id'),
            'company.company_zip_code' => trans('attributes.company.company_zip_code'),
            'company.company_phone' => trans('attributes.company.company_phone'),
            'company.company_fax' => trans('attributes.company.company_fax'),
            'company.company_website' => trans('attributes.company.company_website'),
            'company.company_email' => trans('attributes.company.company_email'),

            //* User attributes
            'user.user_auth_user_id' => trans('attributes.user.user_auth_user_id'),
            'user.user_first_name' => trans('attributes.user.user_first_name'),
            'user.user_last_name' => trans('attributes.user.user_last_name'),
            'user.user_gender' => trans('attributes.user.user_gender'),
            'user.user_address' => trans('attributes.user.user_address'),
            'user.user_village_id' => trans('attributes.user.user_village_id'),
            'user.user_zip_code' => trans('attributes.user.user_zip_code'),
            'user.user_phone' => trans('attributes.user.user_phone')
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void {
        //* Ensure company and user arrays exist
        if (!$this->has('company'))
            $this->merge(['company' => []]);

        if (!$this->has('user'))
            $this->merge(['user' => []]);
    }
}
