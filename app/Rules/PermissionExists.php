<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Components\RBAC\PermissionAwareTrait;
use App\Components\RBAC\Exceptions\InvalidArgumentException;

/**
 * Class PermissionExists
 *
 * @package App\Rules
 */
class PermissionExists implements Rule
{
    use PermissionAwareTrait;

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
        try {
            $this->getPermissionInstance($value);
        } catch (InvalidArgumentException $e) {
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
        return 'Permission doesn\'t exists';
    }
}
