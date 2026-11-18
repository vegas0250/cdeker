# cdeker

[English](README.md) · [Русский](README.ru.md) · [Қазақша](README.kk.md) · [Беларуская](README.be.md) · [Кыргызча](README.ky.md) · **Հայերեն** · [Oʻzbekcha](README.uz.md) · [中文](README.zh.md)

Մինիմալիստական PHP հաճախորդ [CDEK API v2](https://apidoc.cdek.ru/) առաքման ծառայության համար։ Մեկ դաս, առանց կախվածությունների։

## Հնարավորություններ

- API v2-ի ցանկացած մեթոդ `get()`, `post()`, `patch()`, `delete()`, `request()` միջոցով․ փոխանցում եք ուղին և պարամետրերը CDEK-ի փաստաթղթերից։
- OAuth թոքենը ստացվում և քեշավորվում է ավտոմատ (առանձին յուրաքանչյուր միջավայրի և հաշվի համար), թարմացվում է ժամկետի ավարտին կամ `401`-ի դեպքում։
- Սխալները նետվում են որպես `CdekerException`՝ HTTP կարգավիճակով և CDEK-ի `errors` ցուցակով։

## Պահանջներ

- PHP >= 5.6՝ `ext-json` և `ext-openssl` ընդլայնումներով
- `allow_url_fopen = On` (հարցումներն ուղարկվում են PHP հոսքերով, առանց cURL/Guzzle)

## Տեղադրում

```console
composer require vegas0250/cdeker
```

## Արագ մեկնարկ

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

| Մեթոդ | Նկարագրություն |
|-------|----------------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true`՝ `https://api.edu.cdek.ru` թեստային միջավայր |
| `get($path, array $params = [])` | GET, `$params` → հարցման տող․ վերադարձնում է JSON-ից ստացված զանգված |
| `post($path, array $params = [])` | POST, `$params` → JSON մարմին․ վերադարձնում է JSON-ից ստացված զանգված |
| `patch($path, array $params = [])` | PATCH, `$params` → JSON մարմին․ վերադարձնում է JSON-ից ստացված զանգված |
| `delete($path, array $params = [])` | DELETE, `$params` → հարցման տող․ վերադարձնում է JSON-ից ստացված զանգված |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | Ընթացիկ մուտքի թոքենը (քեշից) |

Ոչ JSON հաջող պատասխանները (անդորրագրերի PDF, շտրիխկոդեր) վերադարձվում են որպես չմշակված տող։ `getErrors()`-ը միավորում է վերին մակարդակի `errors`-ը և `requests[].errors`-ը։

## Թոքենի քեշ

Թոքենը պահվում է `sys_get_temp_dir()`-ում՝ `cdeker-token-<hash>.json` ֆայլում (իրավունքներ `0600`), մեկ ֆայլ յուրաքանչյուր «միջավայր + հաշիվ» զույգի համար։ Թարմացվում է ժամկետի ավարտից 60 վայրկյան առաջ․ `401`-ի դեպքում քեշը մաքրվում է, և հարցումը կրկնվում է մեկ անգամ։

## Անցում 1.x-ից 2.0

1.x-ը ցանկացած սխալի դեպքում վերադարձնում էր `false`։ 2.0-ն դրա փոխարեն նետում է `CdekerException`․ փոխարինեք `=== false` ստուգումները `try/catch`-ով։

## Լիցենզիա

[MIT](LICENSE)
