<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class AuthRoleStoreOrUpdateRequest extends AppBaseRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        $roleId = $this->route('authRoleId'); // $this->route('authRoleId')?->auth_role_id

        return [
            // 'auth_role_slug' => ['required', 'string', 'max:150', Rule::unique('auth_roles', 'auth_role_slug')->ignore($roleId, 'auth_role_id')],
            'auth_role_slug' => ['required', 'string', 'max:150'],
            'auth_role_name' => ['required', 'string', 'max:150'],
            'auth_role_is_active' => [Rule::requiredIf($roleId !== null), 'boolean'],
            'auth_role_permissions' => ['required', 'array', 'min:1'],
            // 'auth_role_permissions.*.permission_id' => ['required', 'uuid:7', 'exists:auth_permissions,auth_permission_id'],
            'auth_role_permissions.*.permission_id' => ['required', 'distinct:strict', 'uuid:7'],
            'auth_role_permissions.*.role_permission_parameter' => ['nullable', 'integer', 'between:1,4'],
        ];
    }

    public function attributes() {
        return [
            'auth_role_slug' => trans('attributes.auth_role_slug'),
            'auth_role_name' => trans('attributes.auth_role_name'),
            'auth_role_is_active' => trans('attributes.is_active'),
            'auth_role_permissions' => trans('attributes.auth_role_permissions'),
            'auth_role_permissions.*.permission_id' => trans('attributes.auth_role_permissions_permission_id'),
            'auth_role_permissions.*.role_permission_parameter' => trans('attributes.auth_role_permissions_parameter')
        ];
    }
}
