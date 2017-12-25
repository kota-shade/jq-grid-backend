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

class Where extends BaseHydrator
{
    protected $groupOpMap = [
        'AND' => CompositeExpression::TYPE_AND,
        'OR' => CompositeExpression::TYPE_OR
    ];

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
        $object->andWhere($composite);
        return $object;
    }

    /**
     * @param object $object
     * @return array|void
     * @throws \Exception
     */
    public function extract($object)
    {
        throw new \Exception('Не реализовано');
    }

    /**
     * Формирователь композитного условия, если правила формирования пустые, вернет null
     * @param $data
     * @param QueryBuilder $object
     * @return CompositeExpression|null
     */
    protected function hydrateGroup($data, $object)
    {
        $hasCondition = false;
        $groupOp = $data['groupOp'];
        $operationMethod = $this->groupOpMap[$groupOp];
        $composite = new CompositeExpression($operationMethod);
        if (array_key_exists('rules', $data)) {
            foreach ($data['rules'] as $rule) {
                $this->hydrateRule($rule, $object, $composite);
                $hasCondition = true;
            }
        }
        if (array_key_exists('groups', $data)) {
            foreach ($data['groups'] as $group) {
                if (($res = $this->hydrateGroup($group, $object)) != null) {
                    $composite->add($res);
                    $hasCondition = true;
                }
            }
        }
        if ($hasCondition == false) {
            return null;
        }
        return $composite;
    }

    /**
     * @param $rule
     * @param QueryBuilder $object
     * @param CompositeExpression $composite
     * @return CompositeExpression
     */
    protected function hydrateRule($rule, $object, CompositeExpression $composite)
    {
        $lval = $this->getLval($rule['field']);
        $rval = $rule['data'];
        $expr = $object->expr();
        $exprOp = $rule['op'];
        $res = null;
        switch ($exprOp) {
            case 'eq': //EQ
                $res = $expr->eq($lval, $expr->literal($rval));
                break;
            case 'ne': //NOT,
                $res = $expr->neq($lval, $expr->literal($rval));
                break;
            case 'cn': //LIKE %%
                $res = $expr->like($lval, $expr->literal('%'.$rval.'%'));
                break;
            case 'bw': //RLIKE ^.*
                $res = $expr->like($lval, $expr->literal($rval.'%'));
                break;
            case 'bn': //NOT RLIKE ^.*
                $res = $expr->notLike($lval, $expr->literal($rval.'%'));
                break;
            case 'in': //IN
                $newRval = [];
                array_walk(
                    $rval,
                    function ($item, $key) use ($expr, &$newRval) {
                        $newRval[$item] = $expr->literal($item);
                    });
                $res = $expr->in($lval, implode(',', $newRval));
                break;
            case 'ni': //NOT IN
                $newRval = [];
                array_walk(
                    $rval,
                    function ($item, $key) use ($expr, &$newRval) {
                        $newRval[$item] = $expr->literal($item);
                    });
                $res = $expr->notIn($lval, implode(',', $newRval));
                break;
            case 'ew': //RLIKE .*$
                $res = $expr->like($lval, $expr->literal('%'.$rval));
                break;
            case 'en': //NOT RLIKE .*$
                $res = $expr->notLike($lval, $expr->literal('%'.$rval));
                break;
            case 'nc': //NOT LIKE %%
                $res = $expr->notLike($lval, $expr->literal('%'.$rval.'%'));
                break;
            case 'gt':
                $res = $expr->gt($lval, $expr->literal($rval));
                break;
            case 'ge':
                $res = $expr->gte($lval, $expr->literal($rval));
                break;
            case 'lt':
                $res = $expr->lt($lval, $expr->literal($rval));
                break;
            case 'le':
                $res = $expr->lte($lval, $expr->literal($rval));
                break;
            default:
                //$composite->add($expr->$exprOp($rval, $lval));
        }
        if ($res) {
            $composite->add($res);
        }
        return $composite;
    }

    /**
     * Поле грида может быть дополнено именем таблицы или вообще трансформировано в правильное l-выражение
     * @param $fieldKey
     * @return mixed
     */
    protected function getLval($fieldKey)
    {
        return $fieldKey;
    }
} 