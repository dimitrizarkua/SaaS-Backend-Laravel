<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class ApiResponse
 * Base class for all API responses.
 *
 * @package App\Http\Responses
 */
class ApiResponse extends JsonResponse
{

    /**
     * ApiResponse constructor.
     *
     * @param int   $httpStatusCode HTTP status code.
     * @param mixed $content        Response content.
     * @param array $headers        Response headers.
     */
    public function __construct(int $httpStatusCode = 200, $content = null, array $headers = [])
    {
        parent::__construct($content ?? '', $httpStatusCode, $headers);
    }
}
