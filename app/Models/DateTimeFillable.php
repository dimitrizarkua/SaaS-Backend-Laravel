<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * Trait DateTimeFillable
 *
 * Provides convenient methods to operate different formats when filling datetime attributes from request.
 *
 * @package App\Models
 */
trait DateTimeFillable
{
    /**
     * Set datetime attribute
     *
     * @param string                $attributeName
     * @param \Carbon\Carbon|string $value
     *
     * @return \App\Models\DateTimeFillable
     * @throws \Throwable
     */
    public function setDateTimeAttribute(string $attributeName, $value): self
    {
        if (is_string($value)) {
            $value = Carbon::make($value);
        }
        if ($value !== null && !$value instanceof Carbon) {
            throw new \InvalidArgumentException('Not a valid datetime value');
        }
        $this->attributes[$attributeName] = $value;

        return $this;
    }
}
