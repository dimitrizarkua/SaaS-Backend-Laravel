<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class Password
 *
 * @package App\Rules
 */
class Password implements Rule
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
        $expression = '/^(?=.*[A-Z])(?=.*[ !"#$%&\'()*+\,\-\.\/:;<=>?@[\]^_`{|}~\x5c])(?=.*[0-9])(?=.*[a-z]).{8,}$/';

        return 1 === preg_match($expression, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Password must contain at least one uppercase, one lowercase, one digit and one special character.';
    }
}
