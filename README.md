# cdeker

Реализация http клиента php для быстрой работы с API СДЕК, реализующий  только два 
метода.

* Получение токена;
* Запрос к API.

Не смотря на простоту, cdeker опирается профессиональные 
библиотеки для реализации http запросов [guzzlehttp/guzzle](https://github.com/guzzle/guzzle), 
и хранения токена авторизации в файловом кэше [symfony/cache](https://github.com/symfony/cache).

## Что необходимо для начала работы
* Иметь под рукой целевую документацию [протокол обмена данными (v2.0) от СДЕК](https://api-docs.cdek.ru/)
для сверки с необходимыми методами;
* Согласно документации Account и Secure password.

## Установка
```console
composer require vegas0250/cdeker
```

## Использование

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