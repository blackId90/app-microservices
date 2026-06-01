<?php

namespace App\Http\Requests;

use App\Enums\{UserGenderEnum, UserStatusEnum};
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserStoreOrUpdateRequest extends AppBaseRequest {

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
        $userId = $this->route('userId');

        return [
            //* Auth User fields validation
            'auth_user.auth_user_email' => ['required', 'email', 'max:100'],
            'auth_user.auth_user_username' => ['required', 'string', 'max:100'],
            'auth_user.auth_user_company_id' => ['nullable', 'uuid:7'],
            'auth_user.auth_user_role_id' => ['required', 'uuid:7'],
            'auth_user.auth_user_password' => [Rule::requiredIf($userId === null), 'string', 'min:6', 'max:255', 'confirmed'],
            'auth_user.auth_user_is_admin' => [Rule::requiredIf($userId !== null), 'boolean:strict'],
            'auth_user.auth_user_is_status' => [Rule::requiredIf($userId !== null), new Enum(UserStatusEnum::class)],

            //* User Profile fields validation
            'user.user_first_name' => ['required', 'string', 'max:100'],
            'user.user_last_name' => ['required', 'string', 'max:100'],
            'user.user_gender' => ['required', new Enum(UserGenderEnum::class)],
            'user.user_address' => ['nullable', 'string', 'max:255'],
            'user.user_village_id' => ['nullable', 'integer:strict'],
            'user.user_zip_code' => ['nullable', 'string', 'max:10'],
            'user.user_phone' => ['required', 'string']
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
            'auth_user.auth_user_company_id' => trans('attributes.auth_user.auth_user_company_id'),
            'auth_user.auth_user_role_id' => trans('attributes.auth_user.auth_user_role_id'),
            'auth_user.auth_user_password' => trans('attributes.password'),
            'auth_user.auth_user_is_admin' => trans('attributes.auth_user.auth_user_is_admin'),
            'auth_user.auth_user_is_status' => trans('attributes.auth_user.auth_user_is_status'),

            //* User Profile attributes
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

        if (!$this->has('user'))
            $this->merge(['user' => []]);
    }
}
