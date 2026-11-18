# cdeker

**English** · [Русский](README.ru.md) · [Қазақша](README.kk.md) · [Беларуская](README.be.md) · [Кыргызча](README.ky.md) · [Հայերեն](README.hy.md) · [Oʻzbekcha](README.uz.md) · [中文](README.zh.md)

Minimal PHP client for the [CDEK API v2](https://apidoc.cdek.ru/) delivery service. One class, no dependencies.

## Features

- Any API v2 endpoint via `get()`, `post()`, `patch()`, `delete()`, `request()` — you pass the path and parameters from the CDEK docs.
- OAuth token is obtained and cached automatically (per environment and account), refreshed on expiry or `401`.
- Errors are thrown as `CdekerException` with the HTTP status and CDEK `errors` list.

## Requirements

- PHP >= 5.6 with `ext-json` and `ext-openssl`
- `allow_url_fopen = On` (requests use PHP streams, no cURL/Guzzle)

## Installation

```console
composer require vegas0250/cdeker
```

## Quick start

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

| Method | Description |
|--------|-------------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true` uses `https://api.edu.cdek.ru` |
| `get($path, array $params = [])` | GET, `$params` → query string; returns decoded JSON array |
| `post($path, array $params = [])` | POST, `$params` → JSON body; returns decoded JSON array |
| `patch($path, array $params = [])` | PATCH, `$params` → JSON body; returns decoded JSON array |
| `delete($path, array $params = [])` | DELETE, `$params` → query string; returns decoded JSON array |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | Current access token (cached) |

Non-JSON success responses (receipt PDFs, barcodes) are returned as a raw string. `getErrors()` merges top-level `errors` and `requests[].errors`.

## Token cache

The token is stored in `sys_get_temp_dir()` as `cdeker-token-<hash>.json` (mode `0600`), one file per environment + account. It is renewed 60 s before expiry; on `401` the cache is cleared and the request retried once.

## Upgrading from 1.x to 2.0

1.x returned `false` on any error. 2.0 throws `CdekerException` instead — replace `=== false` checks with `try/catch`.

## License

[MIT](LICENSE)
