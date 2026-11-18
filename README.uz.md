# cdeker

[English](README.md) · [Русский](README.ru.md) · [Қазақша](README.kk.md) · [Беларуская](README.be.md) · [Кыргызча](README.ky.md) · [Հայերեն](README.hy.md) · **Oʻzbekcha** · [中文](README.zh.md)

[CDEK API v2](https://apidoc.cdek.ru/) yetkazib berish xizmati uchun minimalistik PHP mijozi. Bitta klass, bogʻliqliklarsiz.

## Imkoniyatlar

- API v2 ning istalgan metodi `get()`, `post()`, `patch()`, `delete()`, `request()` orqali: CDEK hujjatlaridagi yoʻl va parametrlarni berasiz.
- OAuth tokeni avtomatik olinadi va keshlanadi (har bir muhit va akkaunt uchun alohida), muddati tugaganda yoki `401` boʻlganda yangilanadi.
- Xatolar HTTP holati va CDEK `errors` roʻyxati bilan `CdekerException` sifatida tashlanadi.

## Talablar

- `ext-json` va `ext-openssl` bilan PHP >= 5.6
- `allow_url_fopen = On` (soʻrovlar PHP oqimlari orqali yuboriladi, cURL/Guzzle kerak emas)

## Oʻrnatish

```console
composer require vegas0250/cdeker
```

## Tez boshlash

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

| Metod | Tavsif |
|-------|--------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true` — `https://api.edu.cdek.ru` test muhiti |
| `get($path, array $params = [])` | GET, `$params` → soʻrov satri; JSON dan olingan massivni qaytaradi |
| `post($path, array $params = [])` | POST, `$params` → JSON tanasi; JSON dan olingan massivni qaytaradi |
| `patch($path, array $params = [])` | PATCH, `$params` → JSON tanasi; JSON dan olingan massivni qaytaradi |
| `delete($path, array $params = [])` | DELETE, `$params` → soʻrov satri; JSON dan olingan massivni qaytaradi |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | Joriy kirish tokeni (keshdan) |

JSON boʻlmagan muvaffaqiyatli javoblar (kvitansiya PDF, shtrix-kodlar) xom satr sifatida qaytariladi. `getErrors()` yuqori darajadagi `errors` va `requests[].errors` ni birlashtiradi.

## Token keshi

Token `sys_get_temp_dir()` ichida `cdeker-token-<hash>.json` faylida saqlanadi (huquqlar `0600`), har bir «muhit + akkaunt» juftligi uchun bitta fayl. Muddati tugashidan 60 soniya oldin yangilanadi; `401` boʻlganda kesh tozalanadi va soʻrov bir marta takrorlanadi.

## 1.x dan 2.0 ga oʻtish

1.x har qanday xatoda `false` qaytarardi. 2.0 buning oʻrniga `CdekerException` tashlaydi — `=== false` tekshiruvlarini `try/catch` bilan almashtiring.

## Litsenziya

[MIT](LICENSE)
