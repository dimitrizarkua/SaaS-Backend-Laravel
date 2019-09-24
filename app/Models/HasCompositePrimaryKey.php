<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasCompositePrimaryKeyTrait
 *
 * @package App\Models
 */
trait HasCompositePrimaryKey
{
    protected function setKeysForSaveQuery(Builder $query)
    {
        foreach ($this->primaryKey as $pk) {
            $query = $query->where($pk, $this->attributes[$pk]);
        }

        return $query;
    }
}
