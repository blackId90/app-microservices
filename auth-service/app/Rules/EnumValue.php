<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnumValue implements ValidationRule {
    protected string $enumClass;

    public function __construct(string $enumClass) {
        if (!enum_exists($enumClass))
            throw new \InvalidArgumentException("{$enumClass} is not a valid enum.");

        $this->enumClass = $enumClass;
    }

    /**
     * Run the validation rule.
     *
     * 'type_enum' => ['required', 'string', new EnumValue(EnumClass::class)],
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $validValues = array_column($this->enumClass::cases(), 'value');

        if (!in_array($value, $validValues))
            $fail("The {$attribute} must be one of: " . implode(', ', $validValues));
    }
}
