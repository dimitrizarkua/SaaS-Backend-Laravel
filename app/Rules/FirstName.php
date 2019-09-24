<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class FirstName
 *
 * @package App\Rules
 */
class FirstName implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return 1 === preg_match('/^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}\.]+(?:$|\s+)){1,}$/u', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'First name must contain only letters and dots.';
    }
}
