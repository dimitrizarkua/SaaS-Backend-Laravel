<?php

namespace App\Http\Responses\Error;

/**
 * Class ValidationErrorResponse
 *
 * @OA\Schema(required={"status_code","error_code","error_message","fields"})
 *
 * @package App\Http\Responses\Error
 */
class ValidationErrorResponse extends ApiProblemResponse
{
    /**
     * @OA\Property(description="HTTP status code of the error response", example=422)
     * @var int
     */
    protected $status_code = 422;

    /**
     * @OA\Property(example="invalid_request")
     * @var string
     */
    protected $error_code = 'invalid_request';

    /**
     * @OA\Property(description="A human readable error description, if any")
     * @var string
     */
    protected $error_message = 'Invalid request.';

    /**
     * @OA\Property(description="An optional data (either hash or array) to be passed with the error.")
     * @var object
     */
    protected $data = null;

    /**
     * @OA\Property(description="A hash of field names that have validation errors.")
     * @var object
     */
    protected $fields = null;

    /**
     * ValidationErrorResponse constructor.
     *
     * @param string|null $message Error message.
     * @param mixed|null  $fields  Request fields that have validation errors.
     * @param mixed|null  $data    Any additional data to be passed with the response.
     * @param array       $headers HTTP headers to be set.
     */
    public function __construct($message = null, $fields = null, $data = null, $headers = [])
    {
        parent::__construct(
            $this->status_code,
            $this->error_code,
            $message ?? $this->error_message,
            $fields,
            $data,
            $headers
        );
    }
}
