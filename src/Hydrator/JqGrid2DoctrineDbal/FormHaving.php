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
use Zend\Form\Form;

class FormHaving extends Having
{
    protected $groupOpMap = [
        'AND' => CompositeExpression::TYPE_AND,
        'OR' => CompositeExpression::TYPE_OR
    ];

    /**
     * @var \Zend\Form\Form
     */
    private $form;

    /**
     * @var array
     */
    private $translateMap = null;

    public function __construct($form = null)
    {
        parent::__construct();
        $this->setForm($form);
    }

    /**
     * @return \Zend\Form\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  QueryBuilder $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        if ($this->translateMap == null) {
            $this->prepareTranslationMap($this->form);
        }
        $object = parent::hydrate($data, $object);
        return $object;
    }

    /**
     * Поле грида может быть дополнено именем таблицы или вообще трансформировано в правильное l-выражение
     * @param $fieldKey
     * @return mixed
     */
    protected function getLval($fieldKey)
    {
        if (array_key_exists($fieldKey, $this->translateMap)) {
            return $this->translateMap[$fieldKey];
        }
        return $fieldKey;
    }

    protected function prepareTranslationMap(Form $form)
    {
        if (($baseFS = $form->getBaseFieldset()) == null) {
            throw new \Exception('Не определен базовый филдсет в форме служащей для формирования грида '.get_class($form));
        }
        $translateMap = [];
        /** @var \Zend\Form\Fieldset $element */
        foreach($baseFS as $key => $element) {
            if (($expr = $element->getOption('sql_expr')) != null) {
                $translateMap[$key] = $expr;
            }
        }
        $this->translateMap = $translateMap;
    }
} 