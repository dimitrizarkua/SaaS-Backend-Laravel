<?php

namespace App\Components\Contacts\Exceptions;

use App\Core\ResponseConvertible;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Responses\Error\NotAllowedResponse;

/**
 * Class NotAllowedException
 *
 * @package App\Components\Finance\Exceptions
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
