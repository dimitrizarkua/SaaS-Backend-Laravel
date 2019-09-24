<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\ValidationErrorResponse;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ValidationException
 *
 * @package App\Exceptions\Api
 */
class ValidationException extends ExpectedApiException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new ValidationErrorResponse($this->getMessage(), $this->getErrorFields());
    }

    public static function fromValidator(Validator $validator)
    {
        return new static(
            'There are validation errors',
            'validation_error',
            $validator->fails(),
            $validator->errors()
        );
    }
}
