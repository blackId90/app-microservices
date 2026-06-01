<?php

namespace App\Http\Requests;

use App\Enums\UserGenderEnum;
use Illuminate\Validation\Rules\Enum;

class AuthRegisterRequest extends AppBaseRequest {
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
            //* Auth User fields validation
            'auth_user.auth_user_email' => 'required|email|unique:auth_users,auth_user_email|max:100',
            'auth_user.auth_user_username' => 'required|string|unique:auth_users,auth_user_username|max:100',
            'auth_user.auth_user_password' => 'required|string|min:6|max:255|confirmed',
            'auth_user.auth_user_role_id' => 'nullable|uuid|exists:auth_users,auth_user_role_id',
            'auth_user.auth_user_is_admin' => 'nullable|boolean:strict',

            //* Company fields validation
            'company.company_name' => ['required', 'string', 'max:150'],
            'company.company_address' => ['required', 'string', 'max:255'],
            'company.company_village_id' => ['required', 'integer:strict'],
            'company.company_zip_code' => ['nullable', 'string', 'max:10'],
            'company.company_phone' => ['required', 'string'],
            'company.company_fax' => ['nullable', 'string'],
            'company.company_website' => ['nullable', 'string', 'url:http,https', 'max:100'],
            'company.company_email' => ['required', 'email'],

            //* User fields validation
            // 'user.user_auth_user_id' => ['required', 'uuid'],
            'user.user_first_name' => ['required', 'string', 'max:100'],
            'user.user_last_name' => ['required', 'string', 'max:100'],
            'user.user_gender' => ['required', new Enum(UserGenderEnum::class)],
            'user.user_address' => ['nullable', 'string', 'max:255'],
            'user.user_village_id' => ['nullable', 'integer:strict'],
            'user.user_zip_code' => ['nullable', 'string', 'max:10'],
            'user.user_phone' => ['required', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes() {
        return [
            //* Auth User attributes
            'auth_user.auth_user_email' => trans('attributes.auth_user.auth_user_email'),
            'auth_user.auth_user_username' => trans('attributes.auth_user.auth_user_username'),
            'auth_user.auth_user_password' => trans('attributes.auth_user.auth_user_password'),
            'auth_user.auth_user_role_id' => trans('attributes.auth_user.auth_user_role_id'),
            'auth_user.auth_user_is_admin' => trans('attributes.auth_user.auth_user_is_admin'),

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
            // 'user.user_auth_user_id' => trans('attributes.user.user_auth_user_id'),
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
        //* Ensure auth_user, company and user arrays exist
        if (!$this->has('auth_user'))
            $this->merge(['auth_user' => []]);

        if (!$this->has('company'))
            $this->merge(['company' => []]);

        if (!$this->has('user'))
            $this->merge(['user' => []]);
    }
}

