<?php

namespace App\Components\Operations\Exceptions;

use App\Core\ResponseConvertible;
use App\Http\Responses\Error\NotAllowedResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotAllowedException
 *
 * @package App\Components\Operations\Exceptions
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
