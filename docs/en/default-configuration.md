Default configuration
=========================

Usually the most of project tables has some base design rules and action elements. You can add the special configuration
section inside configuration block 'JqGridBackend' with new key and set this key in form or fieldset options.
Each section must have the keys:

1. template - view template: string
2. options - grid options: array
3. methods - grid methods: array, which will be called during grid creation.

Configuration example:
```php
'JqGridBackend' => [
    'test' => [
        'template' => 'grid/index',
        'options' => [
            'url' => null,
            'datatype' => 'json',
            'multiSort' => true,
        ],
        'methods' => [
        ]
    ],
]
```
If you want to use 'test' configuration set it to form:
```php
     $form->setOption('gridConfigName', 'test');
```
If you don't set it, the 'default' configuration will be used.
The configuration with key 'default' is mandatory in the configuration.

Dynamic elements in configuration.
----------------------------------
Some option may be dynamic elements or the elements, which generates javascript by own rules.
For example the parameter 'pager' depends on form or fieldset name.
In this situation we use class  which is inherited from \Zend\Json\Expr.
The module already has some these classes:

1. JqGridBackend\Grid\View\Helper\Grid\GridPagerId
2. JqGridBackend\Grid\View\Helper\Grid\SubgridPagerId

Example:
```php
'default_1' => [
    'template' => 'grid/index',
    'options' => [
        'url' => null,
        'datatype' => 'json',
        'multiSort' => true,
        'pager' => new Grid\GridPagerId('GridPager-'),
        ....
    ]
```
