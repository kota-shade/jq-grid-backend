<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.06.16
 * Time: 16:57
 */
namespace JqGridBackend\InputFilter;

use Zend\InputFilter;
use Zend\Validator;

class GroupsInputFilter extends InputFilter\InputFilter implements InputFilter\InputFilterInterface
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $groupOp = new InputFilter\Input('groupOp');

        $validator = new Validator\InArray();
        $validator->setHaystack(['AND'=>'AND', 'OR' => 'OR']);
        $groupOp->getValidatorChain()
            ->attach($validator);

        $this->add($groupOp, 'groupOp');

        $rules = new InputFilter\CollectionInputFilter();
        $rules->setInputFilter(new RuleFilter());
        $this->add($rules, 'rules');

        $groups = new LazyCollectionFilter(GroupsInputFilter::class);
        $this->add($groups, 'groups');


    }

}
