# cdeker

[English](README.md) · [Русский](README.ru.md) · [Қазақша](README.kk.md) · [Беларуская](README.be.md) · [Кыргызча](README.ky.md) · [Հայերեն](README.hy.md) · [Oʻzbekcha](README.uz.md) · **中文**

适用于 [CDEK API v2](https://apidoc.cdek.ru/) 快递服务的极简 PHP 客户端。单个类，无依赖。

## 功能

- 通过 `get()`、`post()`、`patch()`、`delete()`、`request()` 调用任意 API v2 接口：传入 CDEK 文档中的路径和参数即可。
- 自动获取并缓存 OAuth 令牌（按环境和账户分别缓存），过期或遇到 `401` 时自动刷新。
- 错误以 `CdekerException` 抛出，包含 HTTP 状态码和 CDEK 返回的 `errors` 列表。

## 环境要求

- PHP >= 5.6，需启用 `ext-json` 和 `ext-openssl`
- `allow_url_fopen = On`（请求通过 PHP 流发送，无需 cURL/Guzzle）

## 安装

```console
composer require vegas0250/cdeker
```

## 快速开始

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

| 方法 | 说明 |
|------|------|
| `new Client($account, $securePassword, $isTest = false)` | `$isTest = true` 使用测试环境 `https://api.edu.cdek.ru` |
| `get($path, array $params = [])` | GET，`$params` → 查询字符串；返回解码后的 JSON 数组 |
| `post($path, array $params = [])` | POST，`$params` → JSON 请求体；返回解码后的 JSON 数组 |
| `patch($path, array $params = [])` | PATCH，`$params` → JSON 请求体；返回解码后的 JSON 数组 |
| `delete($path, array $params = [])` | DELETE，`$params` → 查询字符串；返回解码后的 JSON 数组 |
| `request($path, $method = Client::METHOD_GET, array $params = [])` | `METHOD_GET`, `METHOD_POST`, `METHOD_PATCH`, `METHOD_DELETE` |
| `getTokenNew()` | 当前访问令牌（来自缓存） |

非 JSON 的成功响应（运单 PDF、条形码）以原始字符串返回。`getErrors()` 会合并顶层 `errors` 和 `requests[].errors`。

## 令牌缓存

令牌保存在 `sys_get_temp_dir()` 下的 `cdeker-token-<hash>.json` 文件中（权限 `0600`），每个“环境 + 账户”组合一个文件。在过期前 60 秒刷新；遇到 `401` 时清除缓存并重试一次请求。

## 从 1.x 升级到 2.0

1.x 在任何错误时返回 `false`。2.0 改为抛出 `CdekerException`——请将 `=== false` 判断替换为 `try/catch`。

## 许可证

[MIT](LICENSE)
