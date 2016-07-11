Filters
=======

[jqGrid][] can show filters in table head. This text describe how to configure it in module.
If you want to use filters you should add call method 'filterToolbar' in configuration.
The configuration section 'methods' includes all methods which will call during grid creation on client side.
Each element of array 'methods' is the description for call [jqGrid][] method.
The configuration example:
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
method_1 describe call method 'filterToolbar' with json-parameter, which is created from array.
```php
[
    "searchOnEnter" => true,
    "stringResult" => true,
    "groupOp" => 'AND',
    "searchOperators" => true,
]
```

1. searchOnEnter" => true, - ajax request will send to server after press Enter
2. stringResult" => true, - serach condition will be send in json format like
 advanced search ([look jqGrid documentation](http://www.trirand.com/jqgridwiki/doku.php?id=wiki:toolbar_searching)).
 This allow unify parser on server side.
3. groupOp" => 'AND', - set the condition operation for filter.
4. searchOperators" => true, - true: allow to use comparisons operators is described in grid elements.

Call method syntax id unified. The method list you can
see [here](http://www.trirand.com/jqgridwiki/doku.php?id=wiki:methods).

[jqGrid]: http://jqgrid.com/
