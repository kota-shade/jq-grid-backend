<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.06.16
 * Time: 23:17
 */
namespace JqGridBackend\InputFilter;

use Zend\InputFilter;
use Zend\Validator;

class RuleFilter extends InputFilter\InputFilter
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $field = $this->getField();
        $this->add($field);

        $op = $this->getOp();
        $this->add($op, 'op');

        $data = $this->getData();
        $this->add($data);
    }

    private function getOp()
    {
        $op = new InputFilter\Input('op');
        $validator = new Validator\InArray();
        $validator->setHaystack([
            'eq'=>'eq',
            'ne' => 'ne',
            'cn' => 'cn',
            'bw' => 'bw',
            'bn' => 'bn',
            'ew' => 'ew',
            'en' => 'en',
            'nc' => 'nc'
        ]);
        $validatorChain = $op->getValidatorChain()
            ->attach($validator);
        return $op;
    }

    private function getField()
    {
        $ret = new InputFilter\Input('field');
        //TODO валидаторы добавить
//        $validator = new Validator\Regex('/[:alpha:][:graph:]*/');
//        $validatorChain = $ret->getValidatorChain()
//            ->attach($validator);
        return $ret;
    }

    private function getData()
    {
        $ret = new InputFilter\Input('data');
        return $ret;
    }
}