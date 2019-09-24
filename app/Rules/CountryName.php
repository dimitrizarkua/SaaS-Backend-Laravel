<?php

namespace App\Rules;

use App\Components\Addresses\Helpers\CountryHelper;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class CountryName
 *
 * @package App\Rules
 */
class CountryName implements Rule
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
        return CountryHelper::isCountryExists($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Country doesn\'t exists';
    }
}
