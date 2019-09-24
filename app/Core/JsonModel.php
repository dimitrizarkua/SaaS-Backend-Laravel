<?php

namespace App\Core;

/**
 * Class JsonModel
 *
 * @package App\Core
 */
class JsonModel extends ObjectArrayAccess implements \JsonSerializable
{
    /**
     * Properties to be excluded when serializing model to json.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * JsonModel constructor.
     *
     * @param array|null $properties Optional properties to be set to current instance.
     *
     * @throws \JsonMapper_Exception
     */
    public function __construct(array $properties = null)
    {
        if (!empty($properties)) {
            $this->fillFromJson($properties);
        }
    }

    /**
     * Allows to create new instance of the model with properties filled from input data (JSON).
     *
     * @param null $data                   Input data.
     * @param null $target                 Target object. If null - new instance will be created.
     * @param bool $exceptionOnMissingData Defines whether to throw exception on missing data or not.
     *
     * @return null|object
     * @throws \JsonMapper_Exception
     */
    public static function createFromJson($data = null, $target = null, bool $exceptionOnMissingData = true)
    {
        if (empty($data)) {
            return null;
        }

        $data = static::prepareForDeserialization($data);

        $mapper = new \JsonMapper();
        $mapper->bEnforceMapType = false;
        $mapper->bExceptionOnMissingData = $exceptionOnMissingData;
        $mapper->bStrictObjectTypeChecking = true;

        return $mapper->map($data, $target ?? new static());
    }

    /**
     * Allows to create many models with properties filled from input data (JSON).
     *
     * @param null              $data                   Input data.
     * @param \ArrayObject|null $target                 Target ArrayObject to be filled with the object. If not
     *                                                  specified - new will be created.
     * @param bool              $exceptionOnMissingData Defines whether to throw exception on missing data or not.
     * @param string|null       $className              Class name of the new objects. If not specified, current class
     *                                                  name will be used.
     *
     * @return array
     */
    public static function createManyFromJson(
        $data = null,
        \ArrayObject &$target = null,
        bool $exceptionOnMissingData = true,
        string $className = null
    ): array {
        if (empty($data)) {
            return null;
        }

        $prepared = [];
        foreach ($data as $model) {
            $prepared[] = static::prepareForDeserialization($model);
        }

        $mapper = new \JsonMapper();
        $mapper->bEnforceMapType = false;
        $mapper->bExceptionOnMissingData = $exceptionOnMissingData;
        $mapper->bStrictObjectTypeChecking = true;

        return $mapper->mapArray($prepared, $target ?: [], $className ?? static::class);
    }

    /**
     * Prepares model's data for serialization to JSON.
     *
     * @param array|null $data Hash with keys and values from current instance properties.
     *
     * @return array|null
     */
    public static function prepareForSerialization(array $data = null): ?array
    {
        return $data;
    }

    /**
     * Prepares input data for deserialization.
     *
     * @param array|null $data Input data from JSON.
     *
     * @return array|null
     */
    public static function prepareForDeserialization(?array $data = null): ?array
    {
        return $data;
    }

    /**
     * Fills current instance properties with values from input data.
     *
     * @param array $data                   Input data.
     * @param bool  $exceptionOnMissingData Defines whether to throw exception on missing data or not.
     *
     * @return null|object
     *
     * @throws \JsonMapper_Exception
     */
    public function fillFromJson(array $data, bool $exceptionOnMissingData = true)
    {
        return static::createFromJson($data, $this, $exceptionOnMissingData);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return static::prepareForSerialization($this->toArray());
    }

    /**
     * Serializes object to array.
     *
     * @return array representation of object
     */
    public function toArray(): array
    {
        $properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            $propertyName = $property->name;
            if (!empty($this->hidden) && in_array($propertyName, $this->hidden)) {
                continue;
            }
            $result[$propertyName] = $this->$propertyName;
        }

        return $result;
    }

    /**
     * Replaces hyphens with underscores in field names in JSON.
     *
     * @param string $input JSON string.
     *
     * @return string
     */
    public static function replaceHyphensWithUnderscores(string $input): string
    {
        $result = preg_replace_callback(
            '/\"((?:\w+\-)+\w*)\":/m',
            function ($matches) {
                return sprintf('"%s":', str_replace('-', '_', $matches[1]));
            },
            $input
        );

        return $result;
    }
}
