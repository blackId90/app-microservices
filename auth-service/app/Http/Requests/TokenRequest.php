<?php

namespace App\Http\Requests;

class TokenRequest extends AppBaseRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // return true;
        return $this->attributes->get('isAdmin') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
