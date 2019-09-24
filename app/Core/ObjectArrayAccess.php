<?php

namespace App\Core;

/**
 * Class ObjectArrayAccess
 *
 * @package App\Core
 */
class ObjectArrayAccess implements \ArrayAccess
{
    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}
