<?php

namespace App\Http\Responses;

use Doctrine\Common\Inflector\Inflector;

/**
 * Class ApiErrorResponse
 *
 * @package App\Http\Responses
 */
class ApiErrorResponse extends ApiResponse
{
    protected $errorCode = 'internal_server_error';
    protected $errorMessage = 'Internal error occurred';
    protected $errorData = null;
    protected $errorFields = null;
    protected $referenceId = null;

    /**
     * ApiErrorResponse constructor.
     *
     * @param int         $httpStatusCode HTTP Status code.
     * @param string|null $errorCode      Error code.
     * @param string      $errorMessage   Error message.
     * @param null        $fields         Fields that are invalid in request.
     * @param null        $data           Any additional data to be passed with the error.
     * @param string|null $referenceId    Error reference id.
     * @param array       $headers        Additional headers to be set.
     */
    public function __construct(
        int $httpStatusCode = 500,
        string $errorCode = null,
        string $errorMessage = '',
        $fields = null,
        $data = null,
        string $referenceId = null,
        array $headers = []
    ) {
        if (!$errorCode) {
            $errorCode = self::$statusTexts[$httpStatusCode] ?? null;
        }
        $errorCode = $errorCode ? Inflector::tableize(Inflector::classify(Inflector::ucwords($errorCode))) : null;

        $this->errorCode    = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->errorData    = $data;
        $this->errorFields  = $fields;
        $this->referenceId  = $referenceId;

        $body = [
            'status_code'   => $httpStatusCode,
            'error_code'    => $this->errorCode,
            'error_message' => $this->errorMessage,
        ];

        if ($this->errorFields) {
            $body['fields'] = $this->errorFields;
        }
        if ($this->errorData) {
            $body['data'] = $this->errorData;
        }
        if ($this->referenceId) {
            $body['reference_id'] = $this->referenceId;
        }

        parent::__construct($httpStatusCode, $body, $headers);
    }
}
