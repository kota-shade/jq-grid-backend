<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.06.16
 * Time: 18:45
 */
namespace JqGridBackend\InputFilter;

use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\BaseInputFilter;

class LazyCollectionFilter extends CollectionInputFilter
{

    private $template;

    public function __construct($template)
    {
        $this->setTemplate($template);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($context = null)
    {
        $valid = true;

        if ($this->getCount() < 1) {
            if ($this->isRequired) {
                $valid = false;
            }
        }

        if (is_scalar($this->data)
            || count($this->data) < $this->getCount()
        ) {
            $valid = false;
        }

        if (empty($this->data) || is_scalar($this->data)) {
            $this->clearValues();
            $this->clearRawValues();

            return $valid;
        }

        foreach ($this->data as $key => $data) {
            if (!is_array($data)) {
                $data = [];
            }
            $inputFilter = $this->getInputFilter();
            $inputFilter->setData($data);

            if (null !== $this->validationGroup) {
                $inputFilter->setValidationGroup($this->validationGroup[$key]);
            }

            if ($inputFilter->isValid()) {
                $this->validInputs[$key] = $inputFilter->getValidInput();
            } else {
                $valid = false;
                $this->collectionMessages[$key] = $inputFilter->getMessages();
                $this->invalidInputs[$key] = $inputFilter->getInvalidInput();
            }

            $this->collectionValues[$key] = $inputFilter->getValues();
            $this->collectionRawValues[$key] = $inputFilter->getRawValues();
        }

        return $valid;
    }

    /**
     * Get the input filter used when looping the data
     *
     * @return BaseInputFilter
     */
    public function getInputFilter()
    {
        $template = $this->getTemplate();
        return new $template();
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
}