<?php

use Vegas0250\Cdeker\Client;

require __DIR__.'/vendor/autoload.php';

/**
 * @param string $clientId
 * @param string $clientSecret
 * @param boolean $isTest - Выполнение в тестовой среде, подробнее в документации
 */
$cdekerClient = new Client(
    'BTbhX9bBN9XdPXQDUrBEht0KwUc8Vg0Z',
    'xx7Svztd8cLzzPZcEwbfBNwFkfQIhyQ8',
    false
);

$result = $cdekerClient->get('/v2/location/regions', [
    'size' => 1
]);

print_r($result);

$result = $cdekerClient->post('/v2/calculator/tarifflist', [
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

print_r($result);

$result = $cdekerClient->request('/v2/calculator/tarifflist', Client::METHOD_POST, [
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

print_r($result);