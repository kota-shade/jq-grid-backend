<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 25.07.16
 * Time: 22:31
 */
namespace JqGridBackend\GridAdapterPluginManager;

use Zend\ServiceManager\Exception;

/**
 * Interface GridAdapterPluginManagerInterface
 * @package ExtcivCommon\GridAdapterPluginManager
 */
interface GridAdapterPluginManagerInterface {
    /**
     * {@inheritDoc}
     *
     * @param string $name Service name of plugin to retrieve.
     * @param null|array $options Options to use when creating the instance.
     * @return mixed
     * @throws Exception\ServiceNotFoundException if the manager does not have
     *     a service definition for the instance, and the service is not
     *     auto-invokable.
     * @throws Exception\InvalidServiceException if the plugin created is invalid for the
     *     plugin context.
     */
    public function get($name, array $options = null);
}
