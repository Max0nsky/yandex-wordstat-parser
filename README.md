
<p align="center">
    <h1 align="center">Доступ к методам API Яндекс.Директ - wordstat</h1>
    <br>
</p>


При помощи методов можно получить статистику по ключевым фразам, что может быть полезно для поисковой SEO-оптимизации. Подробности можно узнать в <a href="https://yandex.ru/dev/direct/doc/dg-v4/reference/CreateNewWordstatReport.html" target="_blank"> официальной документации</a> от Яндекс.

Данный класс YandexWordstat предоставляет работу со следующими методами:
<ul>
 <li>CreateNewWordstatReport</li>
 <li>DeleteWordstatReport</li>
 <li>GetWordstatReport</li>
 <li>GetWordstatReportList</li>
</ul>
<br>

Пример использования:
```php
include "YandexWordstat.php";

$token = "YOUR_API_TOKEN";

$phrases = ['купить айфон', 'купить xiaomi'];
$regions = [225];

$yandexWordstat = new YandexWordstat($token);

$resultCreate = $yandexWordstat->createNewWordstatReport($phrases, $regions);
$resultReport = $yandexWordstat->getWordstatReport($resultCreate->data);

var_dump($resultReport);
```
