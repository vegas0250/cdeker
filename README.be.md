# cdeker

[English](README.md) · [Русский](README.ru.md) · [Қазақша](README.kk.md) · **Беларуская** · [Кыргызча](README.ky.md) · [Հայերեն](README.hy.md) · [Oʻzbekcha](README.uz.md) · [中文](README.zh.md)

Мінімалістычны PHP-кліент для службы дастаўкі [CDEK API v2](https://apidoc.cdek.ru/). Адзін клас, без залежнасцей.

## Магчымасці

- Любы метад API v2 праз `get()`, `post()`, `patch()`, `delete()`, `request()`: вы перадаеце шлях і параметры з дакументацыі CDEK.
- OAuth-токен атрымліваецца і кэшуецца аўтаматычна (асобна для кожнага асяроддзя і акаўнта), абнаўляецца пасля заканчэння тэрміну або пры `401`.
- Памылкі выкідваюцца як `CdekerException` з HTTP-статусам і спісам `errors` ад CDEK.

## Патрабаванні

- PHP >= 5.6 з `ext-json` і `ext-openssl`
- `allow_url_fopen = On` (запыты ідуць праз патокі PHP, без cURL/Guzzle)

## Усталяванне

```console
composer require vegas0250/cdeker
```

## Хуткі старт

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

| Метад | Апісанне |
|-------|----------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true` — тэставае асяроддзе `https://api.edu.cdek.ru` |
| `get($path, array $params = [])` | GET, `$params` → радок запыту; вяртае масіў з JSON |
| `post($path, array $params = [])` | POST, `$params` → цела JSON; вяртае масіў з JSON |
| `patch($path, array $params = [])` | PATCH, `$params` → цела JSON; вяртае масіў з JSON |
| `delete($path, array $params = [])` | DELETE, `$params` → радок запыту; вяртае масіў з JSON |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | Бягучы токен доступу (з кэша) |

Паспяховыя адказы не ў JSON (PDF квітанцый, штрыхкоды) вяртаюцца радком як ёсць. `getErrors()` аб'ядноўвае `errors` верхняга ўзроўню і `requests[].errors`.

## Кэш токена

Токен захоўваецца ў `sys_get_temp_dir()` у файле `cdeker-token-<hash>.json` (правы `0600`), па адным файле на пару «асяроддзе + акаўнт». Абнаўляецца за 60 с да заканчэння тэрміну; пры `401` кэш скідаецца, і запыт паўтараецца адзін раз.

## Пераход з 1.x на 2.0

У 1.x пры любой памылцы вярталася `false`. У 2.0 замест гэтага выкідваецца `CdekerException` — заменіце праверкі `=== false` на `try/catch`.

## Ліцэнзія

[MIT](LICENSE)
