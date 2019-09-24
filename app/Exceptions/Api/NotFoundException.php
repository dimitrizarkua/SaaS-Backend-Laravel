<?php

namespace App\Exceptions\Api;

use App\Http\Responses\Error\NotFoundResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotFoundException
 *
 * @package App\Exception
 */
class NotFoundException extends ExpectedApiException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new NotFoundResponse($this->getMessage(), $this->getErrorData());
    }
}
