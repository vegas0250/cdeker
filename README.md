# cdeker

Минималистичный API клиент для службы доставки CDEK API v2.

## Как это работает

Убраны все зависимости, необходим только php 8.2.

## Предварительные данные для начала работы
* Иметь под рукой целевую документацию [протокол обмена данными (v2.0) от СДЕК](https://api-docs.cdek.ru/)
для сверки с необходимыми методами;
* Параметры Account и Secure password (Если вы не зарегистрированы можно воспользоваться тестовыми данными 
из документации).

## Установка
```console
composer require vegas0250/cdeker
```

## Использование

Пример работы всех запросов.

Через ->get(\$url, \$params) реализуются все запросы методом 'GET'.

Через ->post(\$url, \$params) реализуются все запросы методом 'POST'.

Через ->request(\$url, \$method, \$params) можно выбрать любой метод, но по умолчанию выбран 'GET', request 
не является обязательным, и сохраняет обратную совместимость.
 
```php
<?php
$cdekerClient = new Vegas0250\Cdeker\Client( '<clientId>', '<clientSecret>');

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

```