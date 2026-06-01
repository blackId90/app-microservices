<?php

namespace App\Http\Requests;

class InternalDestroyRegisterRequest extends AppBaseRequest {

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
            'company_id' => 'required|uuid|exists:companies,company_id',
            'user_auth_user_id' => 'required|uuid|exists:users,user_auth_user_id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'company_id' => trans('attributes.company.company_id'),
            'user_auth_user_id' => trans('attributes.user.user_auth_user_id'),
        ];
    }

    /**
     * Get parameters from the route so they can be validated by rules()
     */
    protected function prepareForValidation() {
        $this->merge([
            'company_id' => $this->route('company_id'),
            'user_auth_user_id' => $this->route('user_auth_user_id'),
        ]);
    }
}
