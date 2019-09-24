<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface ResponseConvertible
 *
 * @package App\Core
 */
interface ResponseConvertible
{
    /**
     * Converts object to HTTP response.
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse(): Response;
}
