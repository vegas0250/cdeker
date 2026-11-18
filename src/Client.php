<?php

namespace Vegas0250\Cdeker;

class Client
{
    const BASE_URL = 'https://api.cdek.ru';
    const BASE_TEST_URL = 'https://api.edu.cdek.ru';
    const TOKEN_FILE_NAME = 'cdeker-token.json';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    private $isTest = false;

    private $clientId;
    private $clientSecret;

    private $baseUrl;
    private $tokenAbsoluteFile;

    /**
     * @param string $clientId - Это "Account" из документации
     * @param string $clientSecret - Это "Secure password" из документации
     * @param boolean $isTest - Выполнение в тестовой среде, подробнее в документации
     */
    public function __construct($clientId, $clientSecret, $isTest = false) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->isTest = $isTest;
        $this->baseUrl = $this->isTest ? self::BASE_TEST_URL : self::BASE_URL;

        // Отдельный файл кэша на каждую пару "среда + аккаунт"
        $this->tokenAbsoluteFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'cdeker-token-' . md5($this->baseUrl . '|' . $this->clientId) . '.json';
    }

    /**
     * Возвращает действующий access_token (из кэша или новый).
     *
     * @return string
     * @throws CdekerException
     */
    public function getTokenNew() {
        $tokenObject = $this->readCachedToken();

        if ($tokenObject === null) {
            $tokenObject = $this->fetchToken();
            $this->writeCachedToken($tokenObject);
        }

        return $tokenObject['access_token'];
    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     * @throws CdekerException
     */
    public function get($url, $params = []) {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->authorizedRequest(self::METHOD_GET, $url, null);
    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     * @throws CdekerException
     */
    public function post($url, $params = []) {
        return $this->authorizedRequest(self::METHOD_POST, $url, json_encode($params));
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @return array
     * @throws CdekerException
     */
    public function request($url, $method = self::METHOD_GET, $params = []) {
        if ($method == self::METHOD_POST) {
            return $this->post($url, $params);
        }

        return $this->get($url, $params);
    }

    /**
     * Запрос с токеном; при 401 токен сбрасывается и запрос повторяется один раз.
     */
    private function authorizedRequest($method, $url, $content) {
        $token = $this->getTokenNew();

        try {
            return $this->send($method, $url, $this->apiHeaders($token), $content);
        } catch (CdekerException $e) {
            if ($e->getStatusCode() !== 401) {
                throw $e;
            }
        }

        $this->clearCachedToken();

        return $this->send($method, $url, $this->apiHeaders($this->getTokenNew()), $content);
    }

    private function apiHeaders($token) {
        return [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ];
    }

    /**
     * @return array
     * @throws CdekerException
     */
    private function fetchToken() {
        $tokenObject = $this->send(self::METHOD_POST, '/v2/oauth/token', [
            'Content-Type: application/x-www-form-urlencoded',
        ], http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]));

        if (empty($tokenObject['access_token'])) {
            throw new CdekerException('CDEK token response has no access_token', 0, $tokenObject);
        }

        $expiresIn = isset($tokenObject['expires_in']) ? (int)$tokenObject['expires_in'] : 0;
        $tokenObject['expires_at'] = time() + $expiresIn - 60;

        return $tokenObject;
    }

    /**
     * @return array|null Действующий токен или null
     */
    private function readCachedToken() {
        if (!is_file($this->tokenAbsoluteFile)) {
            return null;
        }

        $tokenObject = json_decode((string)@file_get_contents($this->tokenAbsoluteFile), true);

        if (!is_array($tokenObject) || empty($tokenObject['access_token'])
            || !isset($tokenObject['expires_at']) || $tokenObject['expires_at'] <= time()) {
            return null;
        }

        return $tokenObject;
    }

    private function writeCachedToken($tokenObject) {
        // Атомарная запись: временный файл + rename
        $tmp = $this->tokenAbsoluteFile . '.' . uniqid('', true) . '.tmp';

        if (@file_put_contents($tmp, json_encode($tokenObject), LOCK_EX) === false) {
            return;
        }

        @chmod($tmp, 0600);

        if (!@rename($tmp, $this->tokenAbsoluteFile)) {
            @unlink($tmp);
        }
    }

    private function clearCachedToken() {
        if (is_file($this->tokenAbsoluteFile)) {
            @unlink($this->tokenAbsoluteFile);
        }
    }

    /**
     * @param string $method
     * @param string $url Путь относительно baseUrl
     * @param string[] $headers
     * @param string|null $content
     * @return array Декодированный ответ ([] для пустого тела)
     * @throws CdekerException
     */
    private function send($method, $url, $headers, $content) {
        $http = [
            'method' => $method,
            'header' => $headers,
            'ignore_errors' => true,
            'timeout' => 30,
        ];

        if ($content !== null) {
            $http['content'] = $content;
        }

        $response = @file_get_contents($this->baseUrl . $url, false, stream_context_create(['http' => $http]));

        if ($response === false) {
            $error = error_get_last();
            throw new CdekerException('CDEK request failed: ' . ($error ? $error['message'] : 'unknown error'));
        }

        $statusCode = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            // Последняя строка статуса (после возможных редиректов)
            foreach ($http_response_header as $line) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
                    $statusCode = (int)$m[1];
                }
            }
        }

        $body = $response === '' ? [] : json_decode($response, true);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new CdekerException(
                'CDEK API error: HTTP ' . $statusCode . ' ' . $method . ' ' . $url,
                $statusCode,
                $body === null ? $response : $body
            );
        }

        if ($body === null) {
            throw new CdekerException('CDEK returned invalid JSON', $statusCode, $response);
        }

        return $body;
    }
}
