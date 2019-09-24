<?php

namespace App\Components\UsageAndActuals\Exceptions;

use App\Core\ResponseConvertible;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Responses\Error\NotAllowedResponse;

/**
 * Class NotAllowedException
 *
 * @package App\Components\UsageAndActuals\Exceptions
 */
class NotAllowedException extends \RuntimeException implements ResponseConvertible
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new NotAllowedResponse($this->getMessage());
    }
}
