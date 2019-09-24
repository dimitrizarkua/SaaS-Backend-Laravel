<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\NotAllowedResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotAllowedException
 *
 * @package App\Exception
 */
class NotAllowedException extends ExpectedApiException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new NotAllowedResponse($this->getMessage(), $this->getErrorCode(), $this->getErrorData());
    }
}
