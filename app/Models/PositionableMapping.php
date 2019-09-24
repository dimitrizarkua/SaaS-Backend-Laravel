<?php

namespace App\Components\Models;

/**
 * Class PositionableMapping
 *
 * @package App\Components\Models\
 */
class PositionableMapping
{
    /**
     * @var string|null
     */
    protected $parentIdField;

    /**
     * @var string
     */
    protected $idField;

    /**
     * PositionableData constructor.
     *
     * @param string|null $parentIdField Field name which defines foreign key for relationship with parent.
     * @param string      $idField       Field name which defines primary key for the model.
     */
    public function __construct(?string $parentIdField, string $idField = 'id')
    {
        $this->parentIdField = $parentIdField;
        $this->idField       = $idField;
    }

    /**
     * @return string|null
     */
    public function getParentIdField(): ?string
    {
        return $this->parentIdField;
    }

    /**
     * @return string
     */
    public function getIdField(): string
    {
        return $this->idField;
    }
}
