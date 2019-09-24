<?php

namespace App\Components\Finance\Exceptions;

use App\Core\ResponseConvertible;
use App\Http\Responses\Error\ValidationErrorResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ValidationException
 *
 * @package App\Components\Finance\Exceptions
 */
class ValidationException extends \RuntimeException implements ResponseConvertible
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new ValidationErrorResponse($this->getMessage());
    }
}
