<?php

namespace App\Core;

use Doctrine\Common\Inflector\Inflector;

/**
 * Trait Validatable
 *
 * @package App\Core
 */
trait Validatable
{
    /**
     * Validation errors.
     *
     * @var array|null
     */
    private $validationErrors;

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function getValidationRules(): array
    {
        return [];
    }

    /**
     * Composes array (hash where key is property name and value is property value) for validation.
     *
     * @return array
     */
    protected function getValidationData(): array
    {
        $rules = $this->getValidationRules();
        if (empty($rules)) {
            return [];
        }

        $rulesPropertiesNames = array_keys($rules);

        $result = [];
        foreach ($rulesPropertiesNames as $rulesPropertiesName) {
            $propertyName = preg_replace('/\.\*/', '', $rulesPropertiesName);
            $getterMethodName = Inflector::camelize('get_' . $propertyName);

            $result[$propertyName] = method_exists($this, $getterMethodName) ?
                $this->{$getterMethodName}() :
                $this->{$propertyName};
        }

        return $result;
    }

    /**
     * Returns validation errors.
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Returns hash with fields that passed validation (key is property name and value is property value).
     *
     * @return array
     */
    public function getValidFields(): array
    {
        $data = $this->getValidationData();
        if (empty($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $propertyName => $propertyValue) {
            if (!isset($this->validationErrors[$propertyName])) {
                $result[$propertyName] = $propertyValue;
            }
        }

        return $result;
    }

    /**
     * Validates instance properties.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $rules = $this->getValidationRules();

        if (empty($rules)) {
            return true;
        }

        $validator = \Validator::make($this->getValidationData(), $rules);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors();

            return false;
        }

        return true;
    }
}
