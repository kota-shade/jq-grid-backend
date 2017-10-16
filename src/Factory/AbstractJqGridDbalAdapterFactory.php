<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 08.02.17
 * Time: 15:14
 */

namespace JqGridBackend\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use JqGridBackend\Service\JqGridDbalAdapter;

class AbstractJqGridDbalAdapterFactory implements AbstractFactoryInterface
{
    private $baseClass = JqGridDbalAdapter::class;

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return (is_subclass_of($requestedName, $this->baseClass));
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($options === null) {
            $options = [];
        }
        $form = (array_key_exists('form', $options)) ? $options['form'] : null;
        if (array_key_exists('EntityManager', $options)) {
            $em = $options['EntityManager'];
        } else {
            $em = $container->get('doctrine');
        }

        $ret = new $requestedName($container, $form, $em);
        return $ret;
    }
} 