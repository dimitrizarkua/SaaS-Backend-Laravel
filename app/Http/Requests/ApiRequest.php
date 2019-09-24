<?php

namespace App\Http\Requests;

use App\Exceptions\Api\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ApiRequest
 *
 * @package App\Http\Requests
 *
 * @method \App\Models\User user()
 */
abstract class ApiRequest extends FormRequest
{
    /**
     * Array of default values for nullable field.
     * Key of array should be a field name, value - default value that would be set to field.
     *
     * @var array
     */
    protected $defaultValues = [];

    /**
     * Array of fields that should be casted to boolean.
     *
     * @var array
     */
    protected $booleanFields = [];

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidatorInstance()
    {
        $converted = [];

        foreach ($this->booleanFields as $field) {
            $value = $this->get($field, null);
            if (null !== $value) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            $converted[$field] = $value;
        }

        $this->merge($converted);

        return parent::getValidatorInstance();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    abstract public function rules(): array;

    /**
     * Returns list of fillable fields for the model.
     *
     * @return array
     */
    public function getFillableFields(): array
    {
        return array_keys($this->validated());
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw ValidationException::fromValidator($validator);
    }

    /**
     * Returns array of validated fields. Nullable fields will be changed with default values if
     * $defaultValues array is configured.
     *
     * @return array
     */
    public function validated(): array
    {
        $result = parent::validated();
        foreach ($this->defaultValues as $field => $defaultValue) {
            if (!isset($result[$field])) {
                $result[$field] = $defaultValue;
            }
        }

        return $result;
    }
}
