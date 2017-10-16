Это два гидратора которые позволяют преобразовать древовидное условие jqgrid-а пропущенное через
\JqGridBackend\InputFilter\GroupsInputFilter
добавить в запрос, формируемый с помощью

Doctrine\DBAL\Query\QueryBuilder;

в where или having соответственно.

Данные условия не дополняются именем таблицы, если это необходимо наследуемся от данного класса

простейший пример обработки запроса поступившего от jqGrid:

if ($request->getQuery('_search') == true && ($filters = json_decode($request->getQuery('filters'), true)) != false ) {
    $input = new \JqGridBackend\InputFilter\GroupsInputFilter();
    $input->setData($filters);

    if (($res = $input->isValid()) != false) {
        //поступившие данные валидны с точки зрения структуры формирования условия запроса
        $tree = $input->getValues();

        $h = new \JqGridBackend\Hydrator\JqGrid2DoctrineDbal\Where();
        $em = $this->sm->get('doctrine');
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $em->getConnection()->createQueryBuilder();
        $query->select(['a'])
            ->from('zzz', 'A');

        //теперь гидрируем полученное и распарсенное условие в запрос.
        $query = $h->hydrate($tree, $query);
    } else {
        throw new \Exception(print_r($input->getMessages(), true));
    }
}

=====================
FormWhere
------------
Дает возможность превратить имена полей условия поступающие от jqGrid в lvalue - выражения в запросе
для этого нужно в опциях базового филдсета формы, который используется для грида объявить опцию sql_expr, в которую прописать правильное sql выражение
Выражение будет использовано в левой части сравнения.

Пример:
$this->add([
    'name' => 'direction',
    'type' => 'select',
    'options' => [
        'label' => 'Направленность индиктора',
        'value_options' => [
            '1' => 'Положительный',
            '0' => 'Отрицательный',
        ],
        'sql_expr' => 'i_d.id',
        'jqGrid' => [
        ]
    ]
]);

Если в гриде будет выбрано отфильтровать по direction=2
то в запросе появится условие i_d.id=2
