<?php
namespace Vegas0250\Cdeker;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Client
{
    const BASE_URL = 'https://api.cdek.ru';
    const BASE_TEST_URL = 'https://api.edu.cdek.ru';

    private $httpClient;

    private $clientId;
    private $clientSecret;

    /**
     * @param string $clientId - Это "Account" из документации
     * @param string $clientSecret - Это "Secure password" из документации
     * @param boolean $test - Выполнение в тестовой среде, подробнее в документации
     */
    public function __construct($clientId, $clientSecret, $isTest = false) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->httpClient = new \GuzzleHttp\Client([
            'base_uri' => $isTest ? self::BASE_TEST_URL : self::BASE_URL,
            'defaults' => [
                'headers' => [
                    'content-type' => 'application/json'
                ]
            ]
        ]);
    }

    public function getToken() {
        $fileCache = new FilesystemAdapter('cdeker');

        return $fileCache->get('token', function(ItemInterface $item) {
            $rawResponse = $this->httpClient->request('post',self::BASE_URL.'/v2/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]
            ]);

            $response = \json_decode($rawResponse->getBody()->getContents(), true);

            $item->expiresAfter($response['expires_in']);
            $item->set($response['access_token']);

            return $response['access_token'];
        });
    }

    /**
     * @param string $method -  Операясь на документацию находим необходимый
     *                          запрос, например "Список офисов", в описании
     *                          запроса указано GET или POST, в нашем случае
     *                          GET, на данный момент необходимо писать в
     *                          нижнем регистре "get".
     * @param string $url    -  Уникальный адрес запроса, в случае со
     *                          "Списком офисов" получится "/v2/deliverypoints"
     * @param array $params -   Список передаваемых параметров, в виде
     *                          ассоциативного массива.
     */
    public function request($method, $url, $params = []) {
        $token = $this->getToken();

        $rowResponse = $this->httpClient->request($method, self::BASE_URL.$url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
            $method == 'get'? 'query' : 'json' => $params
        ]);

        return \json_decode($rowResponse->getBody()->getContents(), true);
    }

}