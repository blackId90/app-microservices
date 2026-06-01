<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class AuthPermissionSlug implements ValidationRule {

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        //* Hanya izinkan format: kata.kata_ular (alfanumerik, titik, underscore)
        if (!preg_match('/^[a-z0-9]+(\.[a-z0-9_]+)+$/', $value))
            $fail(__('validation.permission_slug', ['attribute' => __('attributes.auth_permission_slug')]));
    }
}
