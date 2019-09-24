<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\TooManyRequestsResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TooManyRequestsException
 *
 * @package App\Exception
 */
class TooManyRequestsException extends ExpectedApiException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new TooManyRequestsResponse($this->getMessage(), $this->getErrorData());
    }
}
