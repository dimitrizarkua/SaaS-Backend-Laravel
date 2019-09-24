<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\ConflictResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConflictExceptionExpected
 *
 * @package App\Exception
 */
class ConflictExceptionExpected extends ExpectedApiException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new ConflictResponse($this->getMessage(), $this->getErrorData());
    }
}
