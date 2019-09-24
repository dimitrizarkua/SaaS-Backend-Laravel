<?php

namespace App\Http\Responses\Error;

/**
 * Class ConflictResponse
 *
 * @OA\Schema(required={"status_code","error_code","error_message"})
 *
 * @package App\Http\Responses\Error
 */
class ConflictResponse extends ApiProblemResponse
{
    /**
     * @OA\Property(description="HTTP status code of the error response", example=409)
     * @var int
     */
    protected $status_code = 409;

    /**
     * @OA\Property(example="conflict")
     * @var string
     */
    protected $error_code = 'conflict';

    /**
     * @OA\Property(description="A human readable error description, if any")
     * @var string
     */
    protected $error_message = 'Conflict.';

    /**
     * @OA\Property(description="An optional data (either hash or array) to be passed with the error.")
     * @var object
     */
    protected $data = null;

    /**
     * ConflictResponse constructor.
     *
     * @param string|null $message Error message.
     * @param mixed|null  $data    Any additional data to be passed with the error.
     */
    public function __construct($message = null, $data = null)
    {
        parent::__construct($this->status_code, $this->error_code, $message ?? $this->error_message, null, $data);
    }
}
