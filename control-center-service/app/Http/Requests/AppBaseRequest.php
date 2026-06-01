<?php

namespace App\Http\Requests;

use App\Exceptions\AccessDeniedException;
use App\Exceptions\ValidationFormRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AppBaseRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    protected function failedAuthorization() {
        throw new AccessDeniedException();
    }

    protected function failedValidation(Validator $validator) {
        $errors = collect($validator->errors()->messages())
            ->map(fn($messages) => $messages[0])
            ->toArray();

        throw new ValidationFormRequestException(context: $errors);
    }
}
