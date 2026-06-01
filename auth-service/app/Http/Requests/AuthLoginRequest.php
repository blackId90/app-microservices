<?php

namespace App\Http\Requests;

class AuthLoginRequest extends AppBaseRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    public function attributes() {
        return [
            'password' => trans('attributes.password')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ];
    }
}
