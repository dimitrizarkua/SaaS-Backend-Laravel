<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\UnauthorizedResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UnauthorizedException
 *
 * @package App\Exceptions\Api
 */
class UnauthorizedException extends ExpectedApiException
{
    public function toResponse(): Response
    {
        return new UnauthorizedResponse();
    }
}
