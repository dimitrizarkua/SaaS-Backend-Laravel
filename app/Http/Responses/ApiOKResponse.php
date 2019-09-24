<?php

namespace App\Http\Responses;

use Illuminate\Support\Collection;

/**
 * Class ApiOKResponse
 *
 * @OA\Schema()
 *
 * @package App\Http\Responses
 */
class ApiOKResponse extends ApiResponse
{
    protected $httpStatusCode = 200;

    /**
     * @OA\Property(property="data", type="object")
     */

    /**
     * @OA\Property(ref="#/components/schemas/Pagination")
     *
     * @var mixed|null
     */
    protected $pagination = null;

    /**
     *
     * @var array|null
     */
    protected $additional = null;

    /**
     * Resource class name to serialize response.
     *
     * @var string
     */
    protected $resource;

    /**
     * Returns response pagination if any
     *
     * @return null|object
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * ApiOKResponse constructor.
     *
     * @param mixed|null $content        Response content.
     * @param mixed|null $pagination     Pagination info.
     * @param int        $httpStatusCode HTTP status code.
     * @param array      $headers        Response headers.
     * @param array      $additional     Additional information for response.
     */
    public function __construct(
        $content = null,
        $pagination = null,
        int $httpStatusCode = 200,
        array $headers = [],
        $additional = []
    ) {
        $this->httpStatusCode = $httpStatusCode;
        $this->pagination     = $pagination;
        $this->additional     = $additional;

        $responseBody = null;

        if (null !== $content) {
            $responseBody = ['data' => $this->serializeContent($content)];
            if ($this->pagination) {
                $responseBody['pagination'] = $this->pagination;
            }

            if ($this->additional) {
                $responseBody['additional'] = $this->additional;
            }
        }

        parent::__construct($this->httpStatusCode, $responseBody, $headers);
    }

    /**
     * Serialize content if specified resource class.
     *
     * @param mixed $content
     *
     * @return mixed
     */
    protected function serializeContent($content)
    {
        if (null === $this->resource) {
            return $content;
        }

        if ($content instanceof Collection) {
            return call_user_func([$this->resource, 'collection'], $content);
        }

        return call_user_func([$this->resource, 'make'], $content);
    }

    /**
     * Creates response instance.
     *
     * @param mixed|null $content        Response content.
     * @param mixed|null $pagination     Pagination info.
     * @param int        $httpStatusCode HTTP status code.
     * @param array      $headers        Response headers.
     * @param mixed|null $additional     Additional data.
     *
     * @return static
     */
    public static function make(
        $content = null,
        $pagination = null,
        int $httpStatusCode = 200,
        array $headers = [],
        $additional = null
    ): self {
        return new static($content, $pagination, $httpStatusCode, $headers, $additional);
    }
}
