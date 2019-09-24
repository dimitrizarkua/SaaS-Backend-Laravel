<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Recurr\Exception\InvalidRRule;

/**
 * Class RecurrenceRule
 *
 * @package App\Rules
 */
class RecurrenceRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $rule
     *
     * @return bool
     */
    public function passes($attribute, $rule)
    {
        try {
            new \Recurr\Rule($rule);
        } catch (InvalidRRule $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Recurrence rule must contain a valid rule string. See https://tools.ietf.org/html/rfc5545.';
    }
}
