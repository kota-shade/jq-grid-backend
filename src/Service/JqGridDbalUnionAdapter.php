<?php

namespace JqGridBackend\Service;

use Interop\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Zend\Form\Form;

class JqGridDbalUnionAdapter extends JqGridDbalAdapter
{
    /** @var  array */
    protected $adapterList = [];

    /**
     * @param ContainerInterface $container
     * @param Form $form
     * @param array $adapterList
     * @param EntityManager $em
     */
    public function __construct(ContainerInterface $container, Form $form = null, array $adapterList = [], EntityManager $em = null)
    {
        $this->sm = $container;
        $this->setAdapterList($adapterList);
        $this->setEntityManager($em);
        $this->setForm($form);
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

        $qItems = [];
        /** @var JqGridDbalAdapter $adapter */
        foreach($this->adapterList as $adapter) {
            $q = $adapter->getQuery($inputData, $quote);
            $q->setFirstResult(null)->setMaxResults(null);
            $qItems[] = $q;

        }

        $query = $this->getQueryBuilder($qItems); //спец билдер умеющий юнион
        $query = $this->addLimit($query, $inputData);
        $query = $this->addOrder($query, $inputData);
        return $query;
    }

    /**
     * @param array $adapterList
     * @return self
     */
    public function setAdapterList(array $adapterList)
    {
        $this->adapterList = $adapterList;
        return $this;
    }

    public function getQueryBuilder(array $qb = [])
    {
        $em = $this->getEntityManager();
        $conn =  $em->getConnection();
        $ret = new UnionQueryBuilder($conn, $qb);
        return $ret;
    }

    /**
     * Возвращает количество записей,подходящих под условия запроса.
     * @param QueryBuilder $query (UnionQueryBuilder)
     * @param $inputData
     * @return mixed
     */
    public function getTotal(QueryBuilder $query, $inputData)
    {
        /** @var UnionQueryBuilder $query */
        $query = clone $query;

        $qbList = $query->getQbList();
        $qbListNew = [];
        /** @var QueryBuilder $subQb */
        foreach($qbList as $subQb) {
            $subQbClone = clone $subQb;
            $subQbClone->setMaxResults(null)
                ->setFirstResult(null)
                ->resetQueryPart('orderBy');
            $qbListNew[] = $subQbClone;
        }
        $query->setQbList($qbListNew)
            ->setMaxResults(null)
            ->setFirstResult(null)
            ->resetQueryPart('orderBy');

        $em = $this->getEntityManager();
        $conn =  $em->getConnection();
        $countQuery = $conn->createQueryBuilder();
        $countQuery->select(['count(*) as cnt'])
            ->from('(' . $query . ') as A');


//        $query->select(['count(*) as cnt']);
//        $query->setMaxResults(null)
//            ->setFirstResult(null)
//            ->resetQueryPart('orderBy');

        $res = $countQuery->execute()->fetch(\PDO::FETCH_ASSOC);

        return $res['cnt'];
    }
}