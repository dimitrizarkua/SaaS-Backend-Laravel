<?php

namespace App\Contracts;

/**
 * Interface ViewDataInterface
 *
 * @package App\Components\Contracts
 */
interface ViewDataInterface
{
    /**
     * Returns array representation of on object.
     *
     * @return array
     */
    public function toArray(): array;
}
