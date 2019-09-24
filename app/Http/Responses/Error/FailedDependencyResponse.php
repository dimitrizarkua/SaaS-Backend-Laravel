<?php

namespace App\Http\Responses\Error;

/**
 * Class FailedDependencyResponse
 *
 * @OA\Schema(required={"status_code","error_code","error_message"})
 *
 * @package App\Http\Responses\Error
 */
class FailedDependencyResponse extends ApiProblemResponse
{
    /**
     * @OA\Property(description="HTTP status code of the error response", example=424)
     * @var int
     */
    protected $status_code = 424;

    /**
     * @OA\Property(example="failed_dependency")
     * @var string
     */
    protected $error_code = 'failed_dependency';

    /**
     * @OA\Property(description="A human readable error description, if any")
     * @var string
     */
    protected $error_message = 'Failed Dependency.';

    /**
     * @OA\Property(description="An optional data (either hash or array) to be passed with the error.")
     * @var object
     */
    protected $data = null;

    /**
     * FailedDependencyResponse constructor.
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
