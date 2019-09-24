<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\FailedDependencyResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FailedDependencyException
 *
 * @package App\Exception
 */
class FailedDependencyException extends ExpectedApiException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new FailedDependencyResponse($this->getMessage(), $this->getErrorCode(), $this->getErrorData());
    }
}
