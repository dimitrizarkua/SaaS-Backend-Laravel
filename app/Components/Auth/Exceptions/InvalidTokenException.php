<?php

namespace App\Components\Auth\Exceptions;

use App\Core\ResponseConvertible;
use App\Http\Responses\Error\NotAllowedResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InvalidTokenException
 *
 * @package App\Components\Auth\Exceptions
 */
class InvalidTokenException extends \RuntimeException implements ResponseConvertible
{
    /**
     * Converts object to HTTP response.
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse(): Response
    {
        return new NotAllowedResponse($this->getMessage());
    }
}
