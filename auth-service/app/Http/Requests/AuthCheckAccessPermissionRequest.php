<?php

namespace App\Http\Requests;

class AuthCheckAccessPermissionRequest extends AppBaseRequest {
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
            'request_route_name' => 'required|string|max:50',
            'request_parameter' => 'nullable|string|max:1',
        ];
    }
}
