<?php

namespace App\Components\Messages\Exceptions;

use App\Core\ResponseConvertible;
use App\Http\Responses\Error\ValidationErrorResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ValidationException
 *
 * @package App\Components\RBAC\Exceptions
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
