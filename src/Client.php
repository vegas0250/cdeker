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
        $this->tokenAbsoluteFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::TOKEN_FILE_NAME;
    }

    public function getTokenNew() {
        if (!file_exists($this->tokenAbsoluteFile)) {
            $response = file_get_contents($this->baseUrl . '/v2/oauth/token', false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query([
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                    ])
                ]
            ]));

            if (!$response) return 0;

            $tokenObject = json_decode($response, true);

            $tokenObject['expires_at'] = time() + $tokenObject['expires_in'] - 60;

            file_put_contents($this->tokenAbsoluteFile, json_encode($tokenObject));

        } else {
            $tokenObject = json_decode(file_get_contents($this->tokenAbsoluteFile),  true);
        }

        if ($tokenObject['expires_at'] < time()) unlink($this->tokenAbsoluteFile);

        return $tokenObject['access_token'];
    }

    public function get($url, $params = []) {
        $response = file_get_contents($this->baseUrl . $url . '?' . http_build_query($params) , false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => ['Authorization: Bearer '.$this->getTokenNew()],
            ]
        ]));

        if ($response) {
            return json_decode($response, true);
        }

        return false;
    }

    public function post($url, $params = []) {
        $response = file_get_contents($this->baseUrl . $url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Authorization: Bearer '.$this->getTokenNew(),
                    'Content-Type: application/json'
                ],
                'content' => json_encode($params)
            ]
        ]));

        if ($response) {
            return json_decode($response, true);
        }

        return false;
    }

    public function request($url, $method = self::METHOD_GET, $params = []) {
        if ($method == self::METHOD_POST) {
            return $this->post($url, $params);
        }

        return $this->get($url, $params);
    }
}