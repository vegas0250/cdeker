# cdeker

[English](README.md) · **Русский** · [Қазақша](README.kk.md) · [Беларуская](README.be.md) · [Кыргызча](README.ky.md) · [Հայերեն](README.hy.md) · [Oʻzbekcha](README.uz.md) · [中文](README.zh.md)

Минималистичный PHP-клиент для службы доставки [СДЭК API v2](https://apidoc.cdek.ru/). Один класс, без зависимостей.

## Возможности

- Любой метод API v2 через `get()`, `post()`, `patch()`, `delete()`, `request()`: передаёте путь и параметры из документации СДЭК.
- OAuth-токен получается и кэшируется автоматически (отдельно для каждой среды и аккаунта), обновляется по истечении срока или при `401`.
- Ошибки выбрасываются как `CdekerException` с HTTP-статусом и списком `errors` от СДЭК.

## Требования

- PHP >= 5.6 с `ext-json` и `ext-openssl`
- `allow_url_fopen = On` (запросы идут через потоки PHP, без cURL/Guzzle)

## Установка

```console
composer require vegas0250/cdeker
```

## Быстрый старт

```php
<?php
use Vegas0250\Cdeker\Client;
use Vegas0250\Cdeker\CdekerException;

require __DIR__ . '/vendor/autoload.php';

// Account and Secure password from your CDEK contract
$client = new Client('<account>', '<secure-password>');

// Test environment (api.edu.cdek.ru): public test credentials from the CDEK docs
$client = new Client(
    'wqGwiQx0gg8mLtiEKsUinjVSICCjtTEP',
    'RmAmgvSgSl1yirlz9QupbzOJVqhCxcP5',
    true
);

try {
    // GET: query parameters
    $regions = $client->get('/v2/location/regions', ['size' => 1]);

    // POST: JSON body
    $tariffs = $client->post('/v2/calculator/tarifflist', [
        'from_location' => ['code' => 248],
        'to_location'   => ['code' => 250],
        'packages'      => ['weight' => 5000],
    ]);

    // PATCH / DELETE, e.g. update or delete an order
    // $client->patch('/v2/orders', ['uuid' => $uuid, 'comment' => '...']);
    // $client->delete('/v2/orders/' . $uuid);

    // Same, method chosen at runtime (default: GET)
    $tariffs = $client->request('/v2/calculator/tarifflist', Client::METHOD_POST, [
        'from_location' => ['code' => 248],
        'to_location'   => ['code' => 250],
        'packages'      => ['weight' => 5000],
    ]);
} catch (CdekerException $e) {
    echo $e->getStatusCode();      // HTTP status, 0 = network error
    print_r($e->getErrors());      // [['code' => ..., 'message' => ...], ...]
    print_r($e->getResponseBody()); // full decoded response
}
```

## API

| Метод | Описание |
|-------|----------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true` — тестовая среда `https://api.edu.cdek.ru` |
| `get($path, array $params = [])` | GET, `$params` → строка запроса; возвращает массив из JSON |
| `post($path, array $params = [])` | POST, `$params` → тело JSON; возвращает массив из JSON |
| `patch($path, array $params = [])` | PATCH, `$params` → тело JSON; возвращает массив из JSON |
| `delete($path, array $params = [])` | DELETE, `$params` → строка запроса; возвращает массив из JSON |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | Текущий токен доступа (из кэша) |

Успешные ответы не в JSON (PDF квитанций, ШК) возвращаются строкой как есть. `getErrors()` объединяет `errors` верхнего уровня и `requests[].errors`.

## Кэш токена

Токен хранится в `sys_get_temp_dir()` в файле `cdeker-token-<hash>.json` (права `0600`), по одному файлу на пару «среда + аккаунт». Обновляется за 60 с до истечения; при `401` кэш сбрасывается и запрос повторяется один раз.

## Переход с 1.x на 2.0

В 1.x при любой ошибке возвращалось `false`. В 2.0 вместо этого выбрасывается `CdekerException` — замените проверки `=== false` на `try/catch`.

## Лицензия

[MIT](LICENSE)
