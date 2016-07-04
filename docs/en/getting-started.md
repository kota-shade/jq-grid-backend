Getting Started
===============
This module contains the view helper, which generates javascript.
The javascript uses [jqGrid][] plugin to create dynamic tables in html pages.
Objects of class Zend\Form\Fieldset or Zend\Form\Form are used for grid declaration.

The simple example
------------------

Form class:
```php
<?php
namespace JqgridTest\Form;

use Zend\Form\Form;

class TestGrid extends Form
{

    public function __construct($name, $options = array()) {
        parent::__construct($name, $options);
        $this->init();
    }

    public function init() {
        $this->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
                'label' => 'Name',
                'search'=> true
            ]
        ]);

        $this->add([
            'name' => 'note',
            'type' => 'text',
            'attributes' => [
                'size' => 10,
                'maxlength' => 20
            ],
            'options' => [
                'label' => 'Note'
            ]
        ]);

        $this->add([
            'name' => 'Select',
            'type' => 'select',
            'options' => [
                'label' => 'TextSelect',
                'value_options' => [
                     '0' => 'French',
                     '1' => 'English',
                     '2' => 'Japanese',
                     '3' => 'Chinese',
                ],
            ]
        ]);
    }

}
```
Create form in controler action and pass it to the view.
Fragment of controller action:
```php
    $form = new Form\TestGrid('Test');
    $form->setOption(
        'gridOptions',
        [
            'url' => '/jqgrid/data',
            'autowidth' => true,
        ]
    );
    return new ViewModel([
        'form' => $form,
    ]);
```
gridOptions - grid options which are different from default parameters. We will get data from [ajax][] request,
  that is why the only 'url' is mandatory.
  We can add any options, which is allowed by [jqGrid][] specification.
  Look options specification [here](http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options).
For example autowidth=>true allows to set grid width equals the width of parent DOM element.

View part:
```php
<link rel="stylesheet" type="text/css" media="screen" href="/css/ui/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/ui/ui.jqgrid.css" />

<script src="/js/grid/grid.locale-ru.js" type="text/javascript"></script>
<script src="/js/grid/jquery.jqGrid.js" type="text/javascript"></script>
<?php
echo $this->jqGrid($this->form);
```
The module doesn't include [jqGrid] and [jQuery]. That is why you should install their in your project yourself.

The form name is used for generation of the grid name.
As the result we can see that fragment on html page:
```javascript
<link rel="stylesheet" type="text/css" media="screen" href="/css/ui/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/ui/ui.jqgrid.css" />

<script src="/js/grid/grid.locale-ru.js" type="text/javascript"></script>
<script src="/js/grid/jquery.jqGrid.js" type="text/javascript"></script>
<table id="GridTest"></table>
<script>
    $(document).ready(function() {
        var grid_pager_id = undefined;
        if (grid_pager_id != undefined) {
            jQuery("table#GridTest").after("<div id=\'" + grid_pager_id + "\'></div>");
        }
        var GridTest = jQuery('#GridTest').jqGrid(
{
    "url": "\/jqgrid\/data",
    "datatype": "json",
    "multiSort": true,
    "autowidth": true,
    "colModel": [
        {
            "edittype": "text",
            "index": "name",
            "label": "Name",
            "name": "name",
            "stype": "text"
        },
        {
            "edittype": "text",
            "index": "note",
            "label": "Note",
            "name": "note",
            "stype": "text"
        },
        {
            "editoptions": {
                "value": {
                    "0": "French",
                    "1": "English",
                    "2": "Japanese",
                    "3": "Chinese"
                }
            },
            "edittype": "select",
            "formatter": "select",
            "index": "Select",
            "label": "TextSelect",
            "name": "Select",
            "searchoptions": {
                "value": {
                    "0": "French",
                    "1": "English",
                    "2": "Japanese",
                    "3": "Chinese"
                }
            },
            "stype": "select"
        }
    ]
});
    });
</script>
```

[ajax]: https://en.wikipedia.org/wiki/AJAX
[composer.json]: ./composer.json
[Composer]: http://getcomposer.org/
[jQuery]: https://jquery.com/
[jqGrid]: http://jqgrid.com/
