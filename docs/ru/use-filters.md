Использование фильтров
======================

jqGrid имеет возможноть размещать под заголовком колонки фильтр, позволяющий фильтровать записи
по условию, выставленному в данном фильтре.
Задание использования в гриде таких фильтров осуществляется с помощью вызова метода грида 'filterToolbar'.
Для задания методов, которые будут вызваны при создании грида используется секция конфигурации 'methods'.
Каждый элемент массива methods является описанием для вызова метода jqGrid.
Пример конфигурации секции для использования фильтров:
```php
    'methods' => [
        /**
         * arr[0] - method name,
         * arr[1...n] - array of parameters (each will be transformed to json)
         */
        'method_1' => [
            'filterToolbar',
            [
                "searchOnEnter" => true,
                "stringResult" => true,
                "groupOp" => 'AND',
                "searchOperators" => true,
            ]
        ]
    ]
```
method_1 описывает вызов метода 'filterToolbar' с json-параметром, созданным из массива
```php
[
    "searchOnEnter" => true,
    "stringResult" => true,
    "groupOp" => 'AND',
    "searchOperators" => true,
]
```
1. searchOnEnter" => true, - ajax запрос на сервер будет послан после нажатия Enter
2. stringResult" => true, - в этом случае условие для поиска данных будет сформировано в виде json-а, как при сложном 
поиске ([см документацию jqGrid](http://www.trirand.com/jqgridwiki/doku.php?id=wiki:toolbar_searching)),
что позволяет унифицировать парсер условия на стороне сервера.
3. groupOp" => 'AND', - задает оператор соединения условий фильтра
4. searchOperators" => true, - true позволяет использовать операторы сравнения заданные в элементах грида
    
Синтаксис вызова методов унифицирован. Список методов можно 
посмотреть [здесь](http://www.trirand.com/jqgridwiki/doku.php?id=wiki:methods).
