Данный набор фильтров позволяет превратить условие выборки из формата jqgrid превратить в в древовидную структуру,
которую затем можно превратить в условие запроса.

простейший пример обработки запроса поступившего от jqGrid:

if ($request->getQuery('_search') == true && ($filters = json_decode($request->getQuery('filters'), true)) != false ) {
    $input = new \JqGridBackend\InputFilter\GroupsInputFilter();
    $input->setData($filters);

    if (($res = $input->isValid()) != false) {
        //поступившие данные валидны с точки зрения структуры формирования условия запроса
        $tree = $input->getValues();
        // в $tree уже имеем древовидную структуру, которую можно преобразовывать с помощью адаптера под тот или иной ORM
    } else {
        throw new \Exception(print_r($input->getMessages(), true));
    }
}