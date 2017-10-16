<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 07.02.17
 * Time: 18:38
 */
namespace JqGridBackend\Hydrator\JqGrid2DoctrineDbal;

use Zend\Hydrator\AbstractHydrator as BaseHydrator;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class Having extends Where
{
    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  QueryBuilder $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        if (($composite = $this->hydrateGroup($data, $object)) == null) {
            return $object;
        }
        $object->andHaving($composite);
        return $object;
    }
} 