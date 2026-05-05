# SpaceBoteque API

## [Описание API](http://sbq.driven.ru/api.html)

Упрощённая надстройка над [Launch Library 2 API](https://thespacedevs.com/llapi) о предстоящих пусках научных миссий по исследованиям Солнечной Системы, Галактики, Вселенной

* `classes/SpaceBoteque.php`: базовый класс
* `classes/SpaceBotequeDBase.php`: родительский класс для сущностей
* `classes/API.php`: класс для API
* `classes/simpleMySQLi.php`: микрокласс для работы с sql через mysqli [github](https://github.com/andybe29/micro/blob/main/simpleMySQLi.php)
* `classes/strUtils.php`: хелпер для работы со строками [github](https://github.com/andybe29/micro/blob/main/strUtils.php)
* `import.php`: скрипт обновления данных с LL2 API
* `www/api.php`: API endpoint
