# cdeker

Минималистичный http клиент на php для быстрой работы с API СДЕК v2, реализующий 
только два метода:

* Получение токена;
* Запрос к API.

## Как это работает

Не смотря на простоту, cdeker опирается на профессиональные 
библиотеки для работы с http запросами [guzzlehttp/guzzle](https://github.com/guzzle/guzzle), 
и хранения токена авторизации в файловом кэше [symfony/cache](https://github.com/symfony/cache).

Вся работа с запросом токена и его хранением умышленно скрыта. Если по какой-то причине
вам необходим именно этот функционал, вы можете просто получить токен, и делать с ним все
что необходимо.

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

Подключение к тестовой среде, и получение списка городов.

```php
<?php

/**
 * Используем данные тестовой учетной записи.
 * 
 * @param string $clientId - Это "Account" из документации
 * @param string $clientSecret - Это "Secure password" из документации
 * @param boolean $test - Выполнение в тестовой среде, подробнее в документации 
 */
$cdekerClient = new Vegas0250\Cdeker\Client(
    'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI', 
    'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG', 
    true
);

/**
 * @param string $method -  Операясь на документацию находим необходимый 
 *                          запрос, например "Список офисов", в описании 
 *                          запроса указано GET или POST, в нашем случае 
 *                          GET, на данный момент необходимо писать в 
 *                          нижнем регистре "get".
 * @param string $url    -  Уникальный адрес запроса, в случае со 
 *                          "Списком офисов" получится "/v2/deliverypoints"
 * @param array $params -   Список передаваемых параметров, в виде
 *                          ассоциативного массива. 
 */
$result = $cdekerClient->request('get', '/v2/deliverypoints', [
    'city_code' => 250
]);

# Выведем ответ
print_r($result);
```

Тоже самое но в боевой среде и без комментариев:

```php
<?php
$cdekerClient = new Vegas0250\Cdeker\Client( '<clientId>', '<clientSecret>');

$cities = $cdekerClient->request('get', '/v2/deliverypoints', [
    'city_code' => 250
])

foreach($cities as $city) {
    // do something
}
```