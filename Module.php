<?php
namespace JqGridBackend;

use Zend\ModuleManager\Feature;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ModuleManager\Listener\ServiceListenerInterface;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\InitProviderInterface
{
    public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				//__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array (
            'abstract_factories' => array (
            ),
            'initializers' => array(
            ),
            'invokables' => array(
            ),
            'factories' => [
                GridAdapterPluginManager\GridAdapterPluginManagerInterface::class => GridAdapterPluginManager\GridAdapterPluginManagerFactory::class,
            ],
        );
    }

    /**
     * Initialize workflow
     *
     * @param  ModuleManagerInterface $manager
     * @return void
     */
    public function init(ModuleManagerInterface $manager) {
        if (!$manager instanceof ModuleManager) {
            $errMsg = sprintf('Module manager not implement %s', ModuleManager::class);
            throw new \InvalidArgumentException($errMsg);
        }
        /** @var ServiceLocatorInterface $sm */
        $sm = $manager->getEvent()->getParam('ServiceManager');
        if (!$sm instanceof ServiceLocatorInterface) {
            $errMsg = sprintf('Service locator not implement %s', ServiceLocatorInterface::class);
            throw new \InvalidArgumentException($errMsg);
        }
        /** @var ServiceListenerInterface $serviceListener */
        $serviceListener = $sm->get('ServiceListener');
        if (!$serviceListener instanceof ServiceListenerInterface) {
            $errMsg = sprintf('ServiceListener not implement %s', ServiceListenerInterface::class);
            throw new \InvalidArgumentException($errMsg);
        }

        $serviceListener->addServiceManager(
            GridAdapterPluginManager\GridAdapterPluginManagerInterface::class,
            GridAdapterPluginManager\GridAdapterPluginManager::CONFIG_KEY,
            GridAdapterPluginManager\GridAdapterProviderInterface::class,
            'getModelConfig'
        );
    }
}
