<?php

namespace App\Contracts;

use App\Components\Models\PositionableMapping;

/**
 * Interface PositionableInterface
 *
 * @package App\Components\Contracts
 */
interface PositionableInterface
{
    /**
     * Returns mapping which needed for positionable observer.
     *
     * @return PositionableMapping|null
     */
    public function getPositionableMapping(): ?PositionableMapping;
}
