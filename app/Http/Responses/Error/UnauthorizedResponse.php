<?php

namespace App\Http\Responses\Error;

/**
 * Class UnauthorizedResponse
 *
 * @OA\Schema(required={"status_code","error_code","error_message"})
 *
 * @package App\Http\Responses\Error
 */
class UnauthorizedResponse extends ApiProblemResponse
{
    /**
     * @OA\Property(description="HTTP status code of the error response", example=401)
     * @var int
     */
    protected $status_code = 401;

    /**
     * @OA\Property(example="unauthorized")
     * @var string
     */
    protected $error_code = 'unauthorized';

    /**
     * @OA\Property(description="A human readable error description, if any", example="Unauthorized.")
     * @var string
     */
    protected $error_message = 'Unauthorized.';

    /**
     * @OA\Property(description="An optional data (either hash or array) to be passed with the error.")
     * @var object
     */
    protected $data = null;

    /**
     * UnauthorizedResponse constructor.
     *
     * @param string|null $message Error message.
     * @param mixed|null  $data    Any additional data to be passed with the error.
     */
    public function __construct($message = null, $data = null)
    {
        parent::__construct($this->status_code, $this->error_code, $message ?? $this->error_message, null, $data);
    }
}
