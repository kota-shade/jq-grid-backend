<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 14.07.16
 * Time: 9:21
 */
namespace JqGridBackend\GridAdapterPluginManager;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception as MyException;

class GridAdapterPluginManagerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     * @throws MyException\ServiceNotFoundException if unable to resolve the service.
     * @throws MyException\ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (array_key_exists(GridAdapterPluginManager::CONFIG_KEY, $config) == false) {
            throw new MyException\ServiceNotCreatedException("Can't find config key=". GridAdapterPluginManager::CONFIG_KEY);
        }

        return new GridAdapterPluginManager($container, $config[GridAdapterPluginManager::CONFIG_KEY]);
    }
}

