<?php

namespace App\Http\Requests;

use App\Enums\{PermissionTargetEnum, PermissionTypeEnum};
use App\Rules\AuthPermissionSlug;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class AuthPermissionStoreOrUpdateRequest extends AppBaseRequest {

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
        $permissionId = $this->route('authPermissionId');

        return [
            'auth_permission_type' => ['required', new Enum(PermissionTypeEnum::class)],
            'auth_permission_parent_permission_id' => ['nullable', 'uuid:7'],
            'auth_permission_slug' => ['required', new AuthPermissionSlug(), 'max:100'],
            'auth_permission_title' => ['required', 'string', 'max:100'],
            'auth_permission_icon' => ['nullable', 'string', 'max:50'],
            'auth_permission_color' => ['nullable', 'hex_color', 'max:50'],
            'auth_permission_url' => ['nullable', 'alpha_dash', 'max:100'],
            'auth_permission_route' => ['required', 'alpha_dash', 'max:100'],
            'auth_permission_target' => ['required', new Enum(PermissionTargetEnum::class)],
            'auth_permission_order' => ['required', 'integer:strict', 'min:1'],
            'auth_permission_is_active' => [Rule::requiredIf($permissionId !== null), 'boolean:strict'],
        ];
    }

    public function attributes() {
        return [
            'auth_permission_type' => trans('attributes.auth_permission_type'),
            'auth_permission_parent_permission_id' => trans('attributes.auth_permission_parent_permission_id'),
            'auth_permission_slug' => trans('attributes.auth_permission_slug'),
            'auth_permission_title' => trans('attributes.auth_permission_title'),
            'auth_permission_icon' => trans('attributes.auth_permission_icon'),
            'auth_permission_color' => trans('attributes.auth_permission_color'),
            'auth_permission_url' => trans('attributes.auth_permission_url'),
            'auth_permission_route' => trans('attributes.auth_permission_route'),
            'auth_permission_target' => trans('attributes.auth_permission_target'),
            'auth_permission_order' => trans('attributes.auth_permission_order'),
            'auth_permission_is_active' => trans('attributes.is_active')
        ];
    }
}
