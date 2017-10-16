<?php
namespace JqGridBackend\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class UnionQueryBuilder extends QueryBuilder
{
    /**
     * @var array
     */
    protected $qbList;
//    /**
//     * The index of the first result to retrieve.
//     *
//     * @var integer
//     */
//    protected $firstResult = null;
//
//    /**
//     * The maximum number of results to retrieve.
//     *
//     * @var integer
//     */
//    protected $maxResults = null;

    /**
     * Initializes a new <tt>QueryBuilder</tt>.
     *
     * @param \Doctrine\DBAL\Connection $connection The DBAL Connection.
     * @param array $qbList
     */
    public function __construct(Connection $connection, array $qbList)
    {
        parent::__construct($connection);
        $this->qbList = $qbList;
    }

    /**
     * @return string
     *
     * @throws \Doctrine\DBAL\Query\QueryException
     */
    public function getSQL()
    {
        $query = '';
        /** @var QueryBuilder $qb */
        foreach($this->qbList as $qb) {
            if ($query != '') {
                $query .= ' UNION ';
            }
            $query .= '(' . $qb->getSQL() . ')';
        }
        $sqlParts = $this->getQueryParts();
        $query .= ($sqlParts['orderBy'] ? ' ORDER BY ' . implode(', ', $sqlParts['orderBy']) : '');
        if ($this->isLimitQuery()) {
            return $this->getConnection()->getDatabasePlatform()->modifyLimitQuery(
                $query,
                $this->getMaxResults(),
                $this->getFirstResult()
            );
        }

        return $query;
    }

    /**
     * @return bool
     */
    protected function isLimitQuery()
    {
        return $this->getMaxResults() !== null || $this->getFirstResult() !== null;
    }

    /**
     * @return array
     */
    public function getQbList()
    {
        return $this->qbList;
    }

    /**
     * @param array $qbList
     * @return self
     */
    public function setQbList($qbList)
    {
        $this->qbList = $qbList;
        return $this;
    }
}
