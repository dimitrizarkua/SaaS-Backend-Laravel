<?php

namespace App\Components\Contacts\Exceptions;

use App\Core\ResponseConvertible;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Responses\Error\ValidationErrorResponse;

/**
 * Class InvalidArgumentException
 *
 * @package App\Components\Contacts\Exceptions
 */
class InvalidArgumentException extends \InvalidArgumentException implements ResponseConvertible
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new ValidationErrorResponse($this->getMessage());
    }
}
