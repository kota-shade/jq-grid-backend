<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 11.02.17
 * Time: 19:47
 */

namespace JqGridBackend\Service;

use Zend\Form\Form;
use Zend\Form\Element as ZFElement;
use Interop\Container\ContainerInterface;
use JqGridBackend\Hydrator\JqGrid2DoctrineDbal\FormWhere as WhereHydrator;
use JqGridBackend\Hydrator\JqGrid2DoctrineDbal\FormHaving as HavingHydrator;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Query\QueryBuilder;

class JqGridDbalAdapter
{
    /** @var ContainerInterface  */
    protected $sm;
    /** @var Form */
    protected $form;
    /** @var  \Doctrine\ORM\EntityManager */
    protected $em;

    public function __construct(ContainerInterface $container, Form $form = null, EntityManager $em = null)
    {
        $this->sm = $container;
        $this->setForm($form);
        $this->setEntityManager($em);
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     * @return self
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     * @return $this
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        $em = $this->getEntityManager();
        return $em->getConnection()->createQueryBuilder();
    }

    protected function addWhere(QueryBuilder $query, $inputData)
    {
        if (isset($inputData['where'])) {
            $hydrator = new WhereHydrator($this->form);
            $query = $hydrator->hydrate($inputData['where'], $query);
        }
        return $query;
    }

    protected function addHaving(QueryBuilder $query, $inputData)
    {
        if (isset($inputData['having'])) {
            $hydrator = new HavingHydrator($this->form);
            $query = $hydrator->hydrate($inputData['having'], $query);
        }
        return $query;
    }

    /**
     * получение массива выражений для включенияв select часть запроса
     * @param bool $quote - квотировать ли элемент (DQL не понимает квотирования, квотит сам)
     * @return array
     * @throws \Exception
     */
    protected function getColumns($quote = true)
    {
        if(($baseFS = $this->form->getBaseFieldset()) == null) {
            throw new \Exception('missing base fieldset in form'. get_class($this->form));
        }
        $fields = [];
        /** @var \Zend\Form\Element $element */
        foreach ($baseFS as $name => $element) {
            $fields[] = $this->getSelectItem($name, $element, $quote);
        }
        return $fields;
    }

    /**
     * получение спецификации поля для включения в select часть запроса
     * @param $name
     * @param \Zend\Form\Element $element
     * @param bool $quote - квотировать ли элемент (DQL не понимает квотирования, квотит сам)
     * @return string
     */
    protected function getSelectItem($name, ZFElement $element, $quote = true)
    {
        if ($quote) {
            $conn = $this->getEntityManager()->getConnection();
            $name = $conn->quoteIdentifier($name);
        }

        if (($sqlExpr = $element->getOption('sql_expr')) != null) {
            return $sqlExpr . ' as ' . $name;
        }
        return $name;
    }

    protected function addLimit(QueryBuilder $query, $inputData)
    {
        $offset = 0;
        $limit = (isset($inputData['rows']))? $inputData['rows'] : 10;
        if (isset($inputData['page']) && isset($inputData['rows'])) {
            $page = intval($inputData['page'])-1;
            $offset = $page * $inputData['rows'];
        }
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        return $query;
    }

    protected function addOrder(QueryBuilder $query, $inputData)
    {
        if (isset($inputData['sidx']) == false || $inputData['sidx']=='') {
            return $query;
        }
        $order = $inputData['sidx'];
        if (isset($inputData['sord'])) {
            $order = trim($order) . ' ' . trim($inputData['sord']);
        }

        //избавляемся от дубликатов
        $orderArray = [];
        $ordArr = explode(',', $order);
        foreach($ordArr as $ordStr) {
            list($name, $type) = explode(' ', trim($ordStr));
            if ($type == null) {
                $type = 'asc';
            }
            $orderArray[$name] = $type;
        }
        foreach($orderArray as $name => $type) {
            $query->addOrderBy($name, $type);
        }
        return $query;
    }

    /**
     * Построение запроса на базе элементов формы,
     * опция sql_from в форме определяет таблицу, из которой будет производиться выборка.
     * @param $inputData
     * @param bool $quote - квотировать ли имена полей и таблиц (DQL не понимает квотирования, квотит сам)
     * @return QueryBuilder
     */
    public function getQuery($inputData, $quote = true)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->getQueryBuilder();
        if (($from = $this->form->getOption('sql_from')) != null) {
            if ($quote) {
                $conn = $this->getEntityManager()->getConnection();
                $from = $conn->quoteIdentifier($from);
            }
            $query->from($from, 't');
        }
        $query->select($this->getColumns($quote));
        $query = $this->addWhere($query, $inputData);
        $query = $this->addLimit($query, $inputData);
        $query = $this->addOrder($query, $inputData);
        return $query;
    }

    /**
     * Возвращает количество записей,подходящих под условия запроса.
     * @param QueryBuilder $query
     * @param $inputData
     * @return mixed
     */
    public function getTotal(QueryBuilder $query, $inputData)
    {
        $query = clone $query;

        $query->select(['count(*) as cnt']);
        $query->setMaxResults(null)
            ->setFirstResult(null)
            ->resetQueryPart('orderBy');

        $res = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        return $res['cnt'];
    }

    public function getData($inputData) {
        $query = $this->getQuery($inputData);
        $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $total = $this->getTotal($query, $inputData);
        $totalPages = intval($total / $inputData['rows']) +1;
        $page = (isset($inputData['page'])) ? $inputData['page'] : 1;
        return [
            'rows' => $rows,
            'records' => $total, //count($rows),
            'total' => $totalPages,
            'page' => $page,
        ];
    }
}