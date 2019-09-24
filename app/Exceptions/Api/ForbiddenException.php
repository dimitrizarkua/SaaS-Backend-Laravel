<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\ForbiddenResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ForbiddenException
 *
 * @package App\Exception
 */
class ForbiddenException extends ExpectedApiException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new ForbiddenResponse($this->getMessage(), $this->getErrorCode(), $this->getErrorData());
    }
}
