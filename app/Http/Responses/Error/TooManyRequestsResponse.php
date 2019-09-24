<?php

namespace App\Http\Responses\Error;

/**
 * Class TooManyRequestsResponse
 *
 * @OA\Schema(required={"status_code","error_code","error_message"})
 *
 * @package App\Http\Responses\Error
 */
class TooManyRequestsResponse extends ApiProblemResponse
{
    /**
     * @OA\Property(description="HTTP status code of the error response", example=429)
     * @var int
     */
    protected $status_code = 429;

    /**
     * @OA\Property(example="too_may_requests")
     * @var string
     */
    protected $error_code = 'too_may_requests';

    /**
     * @OA\Property(description="A human readable error description, if any")
     * @var string
     */
    protected $error_message = 'You have reached requests limit. Please try again later.';

    /**
     * @OA\Property(description="An optional data (either hash or array) to be passed with the error.")
     * @var object
     */
    protected $data = null;

    /**
     * TooManyRequestsResponse constructor.
     *
     * @param string|null $message Error message.
     * @param mixed|null  $data    Any additional data to be passed with the error.
     */
    public function __construct($message = null, $data = null)
    {
        parent::__construct($this->status_code, $this->error_code, $message ?? $this->error_message, null, $data);
    }
}
