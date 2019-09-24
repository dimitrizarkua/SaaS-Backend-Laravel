<?php

namespace App\Http\Responses\Error;

use App\Http\Responses\ApiErrorResponse;

/**
 * Class ApiProblemResponse
 *
 * @package App\Http\Responses\Error
 */
class ApiProblemResponse extends ApiErrorResponse
{
    /**
     * @var int
     */
    protected $status_code;

    /**
     * @var string
     */
    protected $error_code;

    /**
     * @var string
     */
    protected $error_message = 'Internal error occurred';

    /**
     * @var object
     */
    protected $fields = null;

    /**
     * @var object
     */
    protected $data = null;

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getErrorFields()
    {
        return $this->errorFields;
    }

    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * ApiProblemResponse constructor.
     *
     * @param int         $httpStatusCode HTTP Status code.
     * @param string|null $errorCode      Error code.
     * @param string      $errorMessage   Error message.
     * @param mixed|null  $fields         Fields that are invalid in request.
     * @param mixed|null  $data           Any additional data to be passed with the error.
     * @param array       $headers        Additional headers to be set.
     */
    public function __construct(
        int $httpStatusCode = 500,
        string $errorCode = null,
        string $errorMessage = '',
        $fields = null,
        $data = null,
        array $headers = []
    ) {
        parent::__construct($httpStatusCode, $errorCode, $errorMessage, $fields, $data, null, $headers);
    }
}
