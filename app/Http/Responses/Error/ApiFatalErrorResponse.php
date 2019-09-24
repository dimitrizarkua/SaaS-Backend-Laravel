<?php

namespace App\Http\Responses\Error;

use App\Http\Responses\ApiErrorResponse;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class ApiFatalErrorResponse
 *
 * @OA\Schema(type="object")
 *
 * @package App\Http\Responses\Error
 */
class ApiFatalErrorResponse extends ApiErrorResponse
{
    /**
     * @OA\Property(description="HTTP status code of the error response", example=500)
     * @var int
     */
    protected $status_code = 500;

    /**
     * @OA\Property(example="internal_error")
     * @var string
     */
    protected $error_code = "internal_error";

    /**
     * @OA\Property(description="A human readable error description, if any", default="Internal error occurred")
     * @var string
     */
    protected $error_message = 'Internal error occurred';

    /**
     * @OA\Property(description="Reference Id that identifies this error instance.", example="RefID")
     * @var string
     */
    protected $reference_id = null;

    /**
     * ApiFatalErrorResponse constructor.
     *
     * @param int         $httpStatusCode HTTP status code.
     * @param string|null $errorCode      Error code.
     * @param string      $errorMessage   Error message.
     * @param mixed|null  $fields         Request field that are invalid.
     * @param mixed|null  $data           Any additional data to be passed with the error.
     * @param string|null $referenceId    Error reference id.
     */
    public function __construct(
        int $httpStatusCode = 500,
        string $errorCode = null,
        string $errorMessage = '',
        $fields = null,
        $data = null,
        string $referenceId = null
    ) {
        parent::__construct(
            $httpStatusCode ?? $this->status_code,
            $errorCode,
            $errorMessage,
            $fields,
            $data,
            $referenceId
        );
    }

    /**
     * Creates self instance with data obtained from exception.
     *
     * @param \Exception  $exception   Exception.
     * @param string|null $referenceId Reference id if any.
     *
     * @return \App\Http\Responses\Error\ApiFatalErrorResponse
     */
    public static function fromException(\Exception $exception, string $referenceId = null): self
    {
        if (config('app.debug')) {
            $data = [
                'exception' => get_class($exception),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
                'trace'     => collect($exception->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
            ];
        }

        $message = $exception instanceof HttpExceptionInterface ? $exception->getMessage() : 'Server Error';

        return new self(
            500,
            null,
            $message,
            null,
            $data ?? null,
            $referenceId
        );
    }
}
