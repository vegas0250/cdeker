<?php

namespace Vegas0250\Cdeker;

class CdekerException extends \RuntimeException
{
    /** @var int HTTP status (0 = network/transport error) */
    private $statusCode;

    /** @var mixed Decoded response body (array) or raw string, null if none */
    private $responseBody;

    /**
     * @param string $message
     * @param int $statusCode
     * @param mixed $responseBody
     */
    public function __construct($message, $statusCode = 0, $responseBody = null)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * CDEK "errors" list ([['code' => ..., 'message' => ...], ...]) if present.
     *
     * @return array
     */
    public function getErrors()
    {
        if (is_array($this->responseBody) && isset($this->responseBody['errors']) && is_array($this->responseBody['errors'])) {
            return $this->responseBody['errors'];
        }

        return [];
    }
}
