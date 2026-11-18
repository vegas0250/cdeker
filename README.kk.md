# cdeker

[English](README.md) · [Русский](README.ru.md) · **Қазақша** · [Беларуская](README.be.md) · [Кыргызча](README.ky.md) · [Հայերեն](README.hy.md) · [Oʻzbekcha](README.uz.md) · [中文](README.zh.md)

[CDEK API v2](https://apidoc.cdek.ru/) жеткізу қызметіне арналған минималистік PHP клиенті. Бір класс, тәуелділіктерсіз.

## Мүмкіндіктер

- API v2-нің кез келген әдісі `get()`, `post()`, `patch()`, `delete()`, `request()` арқылы: CDEK құжаттамасындағы жол мен параметрлерді бересіз.
- OAuth токені автоматты түрде алынады және кэштеледі (әр орта мен аккаунт үшін бөлек), мерзімі өткенде немесе `401` кезінде жаңартылады.
- Қателер HTTP мәртебесі мен CDEK `errors` тізімі бар `CdekerException` ретінде лақтырылады.

## Талаптар

- `ext-json` және `ext-openssl` бар PHP >= 5.6
- `allow_url_fopen = On` (сұраулар PHP ағындары арқылы жіберіледі, cURL/Guzzle қажет емес)

## Орнату

```console
composer require vegas0250/cdeker
```

## Жылдам бастау

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

| Әдіс | Сипаттама |
|------|-----------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true` — `https://api.edu.cdek.ru` сынақ ортасы |
| `get($path, array $params = [])` | GET, `$params` → сұрау жолы; JSON-нан алынған массивті қайтарады |
| `post($path, array $params = [])` | POST, `$params` → JSON денесі; JSON-нан алынған массивті қайтарады |
| `patch($path, array $params = [])` | PATCH, `$params` → JSON денесі; JSON-нан алынған массивті қайтарады |
| `delete($path, array $params = [])` | DELETE, `$params` → сұрау жолы; JSON-нан алынған массивті қайтарады |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | Ағымдағы қатынау токені (кэштен) |

JSON емес сәтті жауаптар (түбіртек PDF, штрихкодтар) өңделмеген жол ретінде қайтарылады. `getErrors()` жоғарғы деңгейдегі `errors` пен `requests[].errors` біріктіреді.

## Токен кэші

Токен `sys_get_temp_dir()` ішінде `cdeker-token-<hash>.json` файлында сақталады (рұқсаттар `0600`), әр «орта + аккаунт» жұбына бір файл. Мерзімі біткенге дейін 60 секунд бұрын жаңартылады; `401` кезінде кэш тазаланып, сұрау бір рет қайталанады.

## 1.x нұсқасынан 2.0-ге көшу

1.x кез келген қатеде `false` қайтаратын. 2.0 оның орнына `CdekerException` лақтырады — `=== false` тексерістерін `try/catch`-ке ауыстырыңыз.

## Лицензия

[MIT](LICENSE)
