<?php

namespace App\Exceptions\Api;

use App\Core\ResponseConvertible;
use App\Http\Responses\ApiErrorResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExpectedApiException
 *
 * @package App\Exception
 */
class ExpectedApiException extends ApiProblemException implements ResponseConvertible
{
    /**
     * @var string|null
     */
    protected $errorCode = null;

    /**
     * @var mixed|null
     */
    public $errorFields = null;

    /**
     * @var mixed|null
     */
    public $errorData = null;

    /**
     * Returns error code.
     *
     * @return null|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns fields that contains errors.
     *
     * @return mixed|null
     */
    public function getErrorFields()
    {
        return $this->errorFields;
    }

    /**
     * Returns additional data for the error.
     *
     * @return mixed|null
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * ExtendedException constructor.
     *
     * @param string          $message     Error message.
     * @param mixed|null      $errorCode   Error code.
     * @param mixed|null      $errorData   Error data.
     * @param mixed|null      $errorFields Error fields.
     * @param int             $code        HTTP Status code.
     * @param \Exception|null $previous    Previous exception.
     */
    public function __construct(
        $message = '',
        $errorCode = null,
        $errorData = null,
        $errorFields = null,
        $code = 0,
        \Exception $previous = null
    ) {
        $this->errorCode   = $errorCode;
        $this->errorData   = $errorData;
        $this->errorFields = $errorFields;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        return new ApiErrorResponse(
            $this->getCode(),
            $this->getErrorCode(),
            $this->getMessage(),
            $this->getErrorFields(),
            $this->getErrorData()
        );
    }
}
