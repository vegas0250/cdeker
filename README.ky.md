# cdeker

[English](README.md) · [Русский](README.ru.md) · [Қазақша](README.kk.md) · [Беларуская](README.be.md) · **Кыргызча** · [Հայերեն](README.hy.md) · [Oʻzbekcha](README.uz.md) · [中文](README.zh.md)

[CDEK API v2](https://apidoc.cdek.ru/) жеткирүү кызматы үчүн минималисттик PHP кардары. Бир класс, көз карандылыктарсыз.

## Мүмкүнчүлүктөр

- API v2дин каалаган ыкмасы `get()`, `post()`, `patch()`, `delete()`, `request()` аркылуу: CDEK документтеринен жолду жана параметрлерди бересиз.
- OAuth токени автоматтык түрдө алынат жана кэштелет (ар бир чөйрө жана аккаунт үчүн өзүнчө), мөөнөтү бүткөндө же `401` болгондо жаңыланат.
- Каталар HTTP статусу жана CDEK `errors` тизмеси менен `CdekerException` катары ыргытылат.

## Талаптар

- `ext-json` жана `ext-openssl` менен PHP >= 5.6
- `allow_url_fopen = On` (суроо-талаптар PHP агымдары аркылуу жөнөтүлөт, cURL/Guzzle керек эмес)

## Орнотуу

```console
composer require vegas0250/cdeker
```

## Тез баштоо

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

| Ыкма | Сүрөттөмө |
|------|-----------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true` — `https://api.edu.cdek.ru` сыноо чөйрөсү |
| `get($path, array $params = [])` | GET, `$params` → суроо сабы; JSONдон алынган массивди кайтарат |
| `post($path, array $params = [])` | POST, `$params` → JSON денеси; JSONдон алынган массивди кайтарат |
| `patch($path, array $params = [])` | PATCH, `$params` → JSON денеси; JSONдон алынган массивди кайтарат |
| `delete($path, array $params = [])` | DELETE, `$params` → суроо сабы; JSONдон алынган массивди кайтарат |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | Учурдагы кирүү токени (кэштен) |

JSON эмес ийгиликтүү жооптор (дүмүрчөк PDF, штрихкоддор) иштетилбеген сап катары кайтарылат. `getErrors()` жогорку деңгээлдеги `errors` менен `requests[].errors` бириктирет.

## Токен кэши

Токен `sys_get_temp_dir()` ичинде `cdeker-token-<hash>.json` файлында сакталат (уруксаттар `0600`), ар бир «чөйрө + аккаунт» жубуна бир файл. Мөөнөтү бүтөрдөн 60 секунд мурун жаңыланат; `401` болгондо кэш тазаланып, суроо-талап бир жолу кайталанат.

## 1.x версиясынан 2.0гө өтүү

1.x ар кандай катада `false` кайтарчу. 2.0 анын ордуна `CdekerException` ыргытат — `=== false` текшерүүлөрүн `try/catch` менен алмаштырыңыз.

## Лицензия

[MIT](LICENSE)
