Использование субгридов
=======================

[jqGrid][] имеет возможность показывать дочернюю информацию по отношению к записи таблицы.
Подробнее об этом можно прочитать [здесь](http://www.trirand.com/jqgridwiki/doku.php?id=wiki:subgrid).

Для реализации показа дочернего элемента необходимо, чтобы форма(филдсет)
реализовывала интерфейс JqGridBackend\Form\SubgridInterface.
Для удобства присутсвует trait.

Грид может выступать субгридом по отношению к другому гриду.
При этом имеются особенности генерации javascript-овой части.
Поэтому для субгридов по умолчанию используется другой шаблон grid/subgrid, а также другой класс
для генерации pager-а.
По умолчанию для субгрида используется конфигурация 'subgrid'.
Пример конфигурации:
```php
    'subgrid' => [
        'template' => 'grid/subgrid',
        'options' => [
            'datatype' => 'json',
            'multiSort' => true,
            'rowNum' => 20,
            'rowList' => [10,20,30],
            'pager' => new Grid\SubgridPagerId('GridPager-'),
            'viewrecords' => true,
            'caption' =>  null
        ],
        'methods' => [
        ]
    ],
```
При определении, какой хелпер использовать для генерации дочернего элемента, используется мэпинг 'subgridMap'.
Разработчик может добавить другие доступные хелперы для генерации дочерних элементов.
В данном случае, если в качестве субгрида определен объект реализующий FieldsetInterface, то для его генерации
будет использован хелпер Grid
```php
'subgridMap' => [
    /** If we use subgrid describe helpers to convert object to subgrid */
    FieldsetInterface::class => Grid::class,
],
```

[jqGrid]: http://jqgrid.com/