<?php

use Vegas0250\Cdeker\Client;

require __DIR__.'/vendor/autoload.php';

/**
 * @param string $clientId - Это "Account" из документации
 * @param string $clientSecret - Это "Secure password" из документации
 * @param boolean $isTest - Выполнение в тестовой среде, подробнее в документации
 */
$cdekerClient = new Client(
    'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI',
    'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG',
    true
);

$result = $cdekerClient->request('get', '/v2/deliverypoints', [
    'city_code' => 250
]);

var_dump($result);

/*
$result = $cdekerClient->request('get', '/v2/location/cities');
*/

/*
$result = $cdekerClient->request('post', '/v2/calculator/tarifflist', [
    'from_location' => [
        'code' => 248
    ],
    'to_location' => [
        'code' => 250
    ],
    'packages' => [
        'weight' => 5000
    ]
]);
*/


#print_r($result);