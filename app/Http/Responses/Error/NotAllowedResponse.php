<?php

namespace App\Http\Responses\Error;

/**
 * Class NotAllowedResponse
 *
 * @OA\Schema(required={"status_code","error_code","error_message"})
 *
 * @package App\Http\Responses\Error
 */
class NotAllowedResponse extends ApiProblemResponse
{
    /**
     * @OA\Property(description="HTTP status code of the error response", example=405)
     * @var int
     */
    protected $status_code = 405;

    /**
     * @OA\Property(example="not_allowed")
     * @var string
     */
    protected $error_code = 'not_allowed';

    /**
     * @OA\Property(description="A human readable error description, if any")
     * @var string
     */
    protected $error_message = 'Not allowed.';

    /**
     * @OA\Property(description="An optional data (either hash or array) to be passed with the error.")
     * @var object
     */
    protected $data = null;

    /**
     * NotAllowedResponse constructor.
     *
     * @param string|null $message Error message.
     * @param string|null $code    Error code.
     * @param mixed|null  $data    Any additional data to be passed with the error.
     */
    public function __construct($message = null, $code = null, $data = null)
    {
        parent::__construct(
            $this->status_code,
            $code ?? $this->error_code,
            $message ?? $this->error_message,
            null,
            $data
        );
    }
}
