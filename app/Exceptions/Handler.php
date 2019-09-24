<?php

namespace App\Exceptions;

use App\Core\ResponseConvertible;
use App\Exceptions\Api\ExpectedApiException;
use App\Http\Responses\Error\ApiFatalErrorResponse;
use App\Http\Responses\Error\ApiProblemResponse;
use App\Http\Responses\Error\ForbiddenResponse;
use App\Http\Responses\Error\NotAllowedResponse;
use App\Http\Responses\Error\NotFoundResponse;
use App\Http\Responses\Error\TooManyRequestsResponse;
use App\Http\Responses\Error\UnauthorizedResponse;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Handler
 *
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ExpectedApiException::class,
        HttpException::class,
        OAuthServerException::class,
        \App\Components\Jobs\Exceptions\NotAllowedException::class,
        \App\Components\Users\Exceptions\NotAllowedException::class,
        \App\Components\Photos\Exceptions\NotAllowedException::class,
        \App\Components\Finance\Exceptions\NotAllowedException::class,
        \App\Components\Messages\Exceptions\NotAllowedException::class,
        \App\Components\Locations\Exceptions\NotAllowedException::class,
        \App\Components\Office365\Exceptions\NotAllowedException::class,
        \App\Components\Operations\Exceptions\NotAllowedException::class,
        \App\Components\UsageAndActuals\Exceptions\NotAllowedException::class,
        \App\Components\AssessmentReports\Exceptions\NotAllowedException::class,
        \App\Exceptions\Api\FailedDependencyException::class,
    ];

    private $exceptionReferenceId = null;

    private $systemExceptionToAPIResponse = [
        AccessDeniedHttpException::class     => ForbiddenResponse::class,
        NotFoundHttpException::class         => NotFoundResponse::class,
        MethodNotAllowedHttpException::class => NotAllowedResponse::class,
        ThrottleRequestsException::class     => TooManyRequestsResponse::class,
        AuthenticationException::class       => UnauthorizedResponse::class,
        AuthorizationException::class        => [
            'class'   => ForbiddenResponse::class,
            'message' => 'You are not authorized to perform this action.',
        ],
        ModelNotFoundException::class        => [
            'class'   => NotFoundResponse::class,
            'message' => 'Requested resource could not be found.',
        ],
        OAuthServerException::class          => UnauthorizedResponse::class,
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        $this->exceptionReferenceId = Str::uuid();

        if ($this->shouldntReport($exception)) {
            return;
        }

        if (method_exists($exception, 'report')) {
            return $exception->report();
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $exception;
        }

        $logger->error(
            $exception->getMessage(),
            array_merge(
                $this->context(),
                ['exception' => $this->convertExceptionToArrayForReporting($exception)]
            )
        );
    }

    /**
     * @inheritdoc
     */
    protected function context()
    {
        $result = [];

        if (null !== $this->exceptionReferenceId) {
            $result['reference_id'] = $this->exceptionReferenceId;
        }

        $user = Auth::user();
        if ($user) {
            $result['usr'] = [
                'id'    => $user->id,
                'name'  => $user->full_name,
                'email' => $user->email,
            ];
        }

        if (!App::runningInConsole()) {
            $request = [
                'url'    => request()->url(),
                'method' => request()->getMethod(),
            ];
            if (!empty(request()->headers)) {
                $request['headers'] = request()->headers->all();
            }
            if (!empty(request()->query->all())) {
                $request['query'] = request()->query->all();
            }
            if (!empty(request()->getContent())) {
                $request['body'] = request()->getContent();
            }
            $result['request'] = $request;
        }

        return $result;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $exception
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ResponseConvertible) {
            return $exception->toResponse();
        }
        if ($this->canConvertToApiException($exception)) {
            return $this->convertToApiException($exception);
        }

        if ($request->acceptsHtml()) {
            return parent::render($request, $exception);
        }

        return ApiFatalErrorResponse::fromException($exception, $this->exceptionReferenceId);
    }

    /**
     * Convert the given exception to an array for reporting purposes.
     *
     * @param  \Exception $e
     *
     * @return array
     */
    protected function convertExceptionToArrayForReporting(Exception $e)
    {
        return [
            'message'   => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
        ];
    }

    /**
     * Checks if exception can be converted to API response.
     *
     * @param \Exception $exception
     *
     * @return bool
     */
    private function canConvertToApiException(Exception $exception): bool
    {
        return isset($this->systemExceptionToAPIResponse[get_class($exception)]);
    }

    /**
     * Converts exception to API response.
     *
     * @param \Exception $exception
     *
     * @return \App\Http\Responses\Error\ApiProblemResponse
     *
     * @throws \InvalidArgumentException
     */
    private function convertToApiException(Exception $exception): ApiProblemResponse
    {
        if (!$this->canConvertToApiException($exception)) {
            throw new \InvalidArgumentException('Can\'t convert exception to API response.');
        }

        $class  = $this->systemExceptionToAPIResponse[get_class($exception)];
        $config = [];
        if (is_string($class)) {
            $config['class'] = $class;
        } elseif (is_array($class)) {
            $config = $class;
            if (false === array_key_exists('class', $config)) {
                throw new \InvalidArgumentException('Config should contain class key');
            }
        } else {
            throw new \InvalidArgumentException('Config should be a string or an array');
        }

        $responseClass = $config['class'];
        $message       = !empty($exception->getMessage()) ? $exception->getMessage() : null;

        return new $responseClass($config['message'] ?? $message);
    }
}
