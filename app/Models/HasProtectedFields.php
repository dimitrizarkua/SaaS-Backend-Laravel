<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasProtectedFields
 *
 * @package App\Models
 * @mixin Model
 */
trait HasProtectedFields
{
    public function __set($key, $value)
    {
        if (!property_exists($this, 'protectedFields')) {
            $this->protectedFields = [];
        }

        $existingValue = $this->getAttributeValue($key);
        if (in_array($key, $this->protectedFields) && null !== $existingValue) {
            return;
        }

        return parent::__set($key, $value);
    }
}
