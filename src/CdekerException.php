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
     * CDEK errors ([['code' => ..., 'message' => ...], ...]): top-level "errors"
     * plus "requests[].errors" (orders, intakes, webhooks).
     *
     * @return array
     */
    public function getErrors()
    {
        $errors = [];

        if (!is_array($this->responseBody)) {
            return $errors;
        }

        if (isset($this->responseBody['errors']) && is_array($this->responseBody['errors'])) {
            $errors = $this->responseBody['errors'];
        }

        if (isset($this->responseBody['requests']) && is_array($this->responseBody['requests'])) {
            foreach ($this->responseBody['requests'] as $request) {
                if (isset($request['errors']) && is_array($request['errors'])) {
                    $errors = array_merge($errors, $request['errors']);
                }
            }
        }

        return $errors;
    }
}
