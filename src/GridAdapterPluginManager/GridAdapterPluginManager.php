<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 25.07.16
 * Time: 22:33
 */
namespace JqGridBackend\GridAdapterPluginManager;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class GridAdapterPluginManager
 * @package ExtcivCommon\GridAdapterPluginManager
 */
class GridAdapterPluginManager extends AbstractPluginManager implements GridAdapterPluginManagerInterface
{
    const CONFIG_KEY = 'grid_adapter_manager';

    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        parent::__construct($configInstanceOrParentLocator, $config);
    }

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws \RuntimeException if invalid
     */
    public function validate($plugin)
    {
        if (is_object($plugin) ) {
            return;
        }
        throw new \RuntimeException('Can not load grid_adapter plugin');
    }
}