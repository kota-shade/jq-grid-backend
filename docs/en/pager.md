Pager, notes about script generation.
====================================

For using jqGrid pager we should set selector of the div element.
The pager is dynamic element, it generate script which depends of form name.
That is why we use Grid\GridPagerId, which inherits \Zend\Json\Expr.
Method __toString() generates pager selector.
Grid configuration example with pager:
```php
        'default_1' => [
            'template' => 'grid/index',
            'options' => [
                'url' => null,
                'datatype' => 'json',
                'multiSort' => true,
                'pager' => new Grid\GridPagerId('GridPager-'),
                'rowNum' => 10,
                'rowList' => [10,20,30],
                'viewrecords' => true,
            ],
            'methods' => [
            ]
        ],
```

1. pager  => new Grid\GridPagerId('GridPager-'), set pager selector with prefix 'GridPager-'.
 Current helper view-script create pager DOM element with right identifier and generate javascript with right selector.
2. rowNum - set the default number of row to show
3. rowList - set the list for choosing how many rows to show on one page
4. viewrecords - if true, show additional information about rows range to show.

The details about this options you can read [here]
Подробности этих опций и о других опциях грида можно (http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options).
If we use pager in subgrid we use Grid\SubgridPagerId instead Grid\GridPagerId (see configuration example).

Notes about script generation
-----------------------------
View helper uses view script for javascript generation. This view script is set in configuration on 'template' option.
The default grid template is 'grid/index'. The default subgrid template is 'grid/subgrid'.
Usually it will be enough for most cases.


