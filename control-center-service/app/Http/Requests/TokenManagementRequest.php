<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class TokenManagementRequest extends AppBaseRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // return false;
        return $this->attributes->get('isAdmin') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'user_id' => 'required|uuid',
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'user_id' => $this->route('authUserId')
        ]);
    }
}
