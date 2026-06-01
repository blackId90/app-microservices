<?php

namespace App\Http\Requests;

use App\Enums\UserStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class AuthUserStoreOrRegisterRequest extends AppBaseRequest {
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
        $userId = $this->route('authUserId');

        return [
            'auth_user_email' => ['required', 'email', 'max:100'],
            'auth_user_username' => ['required', 'string', 'max:100'],
            'auth_user_company_id' => ['nullable', 'uuid:7'],
            // 'auth_user_role_id' => ['nullable', 'uuid:7', 'exists:auth_users,auth_user_role_id'],
            'auth_user_role_id' => ['required', 'uuid:7'],
            // 'auth_user_password' => [Rule::requiredIf($userId === null), 'string', 'min:6', 'max:255', 'confirmed'],
            'auth_user_password' => [Rule::requiredIf($userId === null), 'string', 'min:6', 'max:255'],
            'auth_user_is_admin' => [Rule::requiredIf($userId !== null), 'boolean:strict'],
            'auth_user_is_status' => [Rule::requiredIf($userId !== null), new Enum(UserStatusEnum::class)],
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
            'auth_user_email' => trans('attributes.auth_user.auth_user_email'),
            'auth_user_username' => trans('attributes.auth_user.auth_user_username'),
            'auth_user_password' => trans('attributes.auth_user.auth_user_password'),
            'auth_user_role_id' => trans('attributes.auth_user.auth_user_role_id'),
            'auth_user_is_admin' => trans('attributes.auth_user.auth_user_is_admin'),
            'auth_user_is_status' => trans('attributes.auth_user.auth_user_is_status')
        ];
    }
}
