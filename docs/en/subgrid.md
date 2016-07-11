Subgrids
========

[jqGrid][] can show children information connected with current record.
The detail documentation is [here](http://www.trirand.com/jqgridwiki/doku.php?id=wiki:subgrid).

If you want to show children data implements JqGridBackend\Form\SubgridInterface in you form/fieldset.
There is a trait for this.

Grid can be a subgrid for another grid. There is some specific in javascript generation for it.
That is why there is another template 'grid/subgrid' and another pager class.
Default configuration for subgrid is 'subgrid'.
Look the example:
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
To find the helper for generation children element we use map 'subgridMap' from configuration.
The developer can add other available helpers for generation children elements.
In this case if object for subgrid implements FieldsetInterface, we use helper 'Grid' for subgrid generation.

```php
'subgridMap' => [
    /** If we use subgrid describe helpers to convert object to subgrid */
    FieldsetInterface::class => Grid::class,
],
```

[jqGrid]: http://jqgrid.com/
