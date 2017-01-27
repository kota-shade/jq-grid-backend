<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 07.04.16
 * Time: 0:35
 */

namespace JqGridBackend\Grid\View\Helper;

use JqGridBackend\Form\SubgridInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\FieldsetInterface as GridObject;
use Zend\ServiceManager\AbstractPluginManager;
use JqGridBackend\Grid\View\Helper\Grid\GridObjectAwareInterface;
use JqGridBackend\Grid\View\Helper\ColModelAdapterPluginManagerInterface;
use JqGridBackend\Exception;
use Zend\Json\Expr as JsonExpr;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\Form\Element as FormElement;

class Grid extends AbstractHelper
{
    /** @var  AbstractPluginManager */
    protected $pluginManager;

    /**
     * @var array
     */
    protected $config;

    protected $adapterMapConfig;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var GridObject
     */
    protected $obj;

    /**
     * default configuration name
     * @var string
     */
    protected $configName = 'default';

    /**
     * cumulative grid options
     * @var array
     */
    protected $gridOptions;

    /**
     * @var array
     */
    protected $gridMethods = [];

    /**
     * @var ColModelAdapterPluginManagerInterface
     */
    protected $colModelAdapterPluginManager;

    public function __construct(AbstractPluginManager $pluginManager, $colModelAdapterPluginManager, $config)
    {
        $this->setPluginManager($pluginManager);
        $this->setColModelAdapterPluginManager($colModelAdapterPluginManager);
        $this->config = $config;
        $this->adapterMapConfig = $this->getConfigKey('adapterMap');
    }

    /**
     * @param GridObject $form
     * @param array $options
     * @return $this|string
     */
    public function __invoke(GridObject $form = null, array $options = [])
    {
        if (!$form) {
            return $this; //FIXME
        }

        return $this->render($form, $options);
    }

    /**
     * @param GridObject $obj
     * @param array $options
     * @return string
     * @throws \Exception
     */
    public function render(GridObject $obj, array $options = [])
    {
        foreach ($options as $k => $v) {
            switch ($k) {
                case 'configName':
                    $this->setConfigName($v);
                    break;
            }
        }

        $this->setObj($obj);
        $this->setName($obj->getName());
        //NB override configName if defined in object
        if (($configName = $obj->getOption('gridConfigName')) != null) {
            $this->setConfigName($configName);
        }
        $this->setGridOptions($obj);
        $this->setGridMethods($obj);

        $renderer = $this->getRenderer();
        $viewModel = new ViewModel([
            'grid' => $this,
            'object' => $obj,
            'options' => $options
        ]);
        $viewModel->setTemplate($this->getCurrentConfigKey('template'));
        $ret = $renderer->render($viewModel);
        return $ret;

    }

    public function getBody()
    {
        $obj = $this->getObj();
        $ret = array_merge(
            $this->getGridOptions($obj),
            [
                'colModel' => $this->getColModel($obj)
            ],
            $this->getSubgridOptions($obj)
        );
        return $ret;
    }


    /**
     * get grid options from object. They are preffered over config
     * @param GridObject $obj
     * @return array
     */
    private function getObjectGridOptions(GridObject $obj)
    {
        if (($gridOptions = $obj->getOption('gridOptions')) == null) {
            return [];
        }
        return $gridOptions;
    }

    /**
     * get grid methods from object. They are preffered over methods from config
     * @param GridObject $obj
     * @return array|mixed|null
     */
    private function getObjectGridMethods(GridObject $obj)
    {
        if (($gridMethods = $obj->getOption('gridMethods')) == null) {
            return [];
        }
        return $gridMethods;
    }

//    /**
//     * Get configuration section name for current grid
//     * @param array $gridOptions
//     * @return string
//     */
//    private function getGridConfigName(array $gridOptions)
//    {
//        $configName = (array_key_exists('configName', $gridOptions)) ? $gridOptions['configName'] : $this->getConfigName();
//        return $configName;
//    }

    private function getColModel(GridObject $obj)
    {
        $colModel = array();
        /** @var FormElement $column */
        foreach ($obj as $column) {
            $adapter = $this->getColModelAdapter($column);
            $colModel[] = $adapter($column);
        }
        return $colModel;
    }

    /**
     * get column adapter from column object
     * @param FormElement $column
     * @return ColModel\ColModelAdapter
     */
    protected function getColModelAdapter(FormElement $column)
    {
        /** @var string $adapterName */
        $adapterName = $this->getAdapterName($column);
        /** @var AbstractPluginManager $adapterPM */
        $adapterPM = $this->getColModelAdapterPluginManager();
        /** @var ColModel\ColModelAdapter $colModelAdapter */
        $colModelAdapter = $adapterPM->get($adapterName);
        return $colModelAdapter;
    }

    private function getDefaultOptions(GridObject $obj, $optionsKey = 'default')
    {

        $config = $this->getCurrentConfigKey('options');
        if (is_array($config) == false) {
            throw new Exception\InvalidArgumentException('missing "options" in '.$optionsKey.' configuration for JqGridBackend');
        }
        $ret = [];

        foreach ($config as $k => $v) {
            if ($v instanceof GridObjectAwareInterface) {
                $v->setGridObject($obj);
            }
            $ret[$k] = $v;
        }

        return $ret;
    }

    private function getDefaultMethods(GridObject $obj, $methodsKey = 'default')
    {
        //TODO валидаторы на методы
        $config = $this->getCurrentConfigKey('methods');
        if (is_array($config) == false) {
            throw new Exception\InvalidArgumentException('missing "methods" in '.$methodsKey.' configuration for JqGridBackend');
        }
        $ret = [];
        foreach ($config as $k => $v) {
            if (is_array($v) && count($v)>0) {
                $name = array_shift($v);
                $v = new Grid\Method($name, $v);
            }
            if ($v instanceof GridObjectAwareInterface) {
                $v->setGridObject($obj);
            }
            $ret[$k] = $v;
        }
        return $ret;
    }

    protected function getSubgridOptions($obj)
    {
        $ret = [];

        /** @var GridObject $obj */
        if ($obj instanceof SubgridInterface == false) {
            return $ret;
        }
        /** @var SubgridInterface $obj */
        if (($subgridObj = $obj->getSubgrid()) == null) {
            return $ret;
        }

        /** @var \Callable $subgridHelper */
        $subgridHelper = $this->getSubgridHelper($subgridObj);
        $result = $subgridHelper(
            $subgridObj,
            [
                'configName' => 'subgrid',
            ]
        );

        $ret['subGrid'] = true;
        $ret['subGridRowExpanded'] = new JsonExpr($result);

        return $ret;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return PhpRenderer
     */
    private function getRenderer()
    {
        $renderer = clone $this->getView();
        $renderer->setVars([]);
        return $renderer;
    }

    /**
     * get key from global configuration
     * @param $key
     * @return mixed|null
     */
    protected function getConfigKey($key)
    {
        if (array_key_exists($key, $this->config) == false) {
            return null;
        }

        return $this->config[$key];
    }

    /**
     * @param $key
     * @return null|array
     */
    protected function getCurrentConfigKey($key)
    {
        $configName = $this->getConfigName();
        $curConfig = $this->getConfigKey($configName);
        if ($curConfig == null) {
            throw new Exception\InvalidArgumentException('missing "'.$configName.'" section in JqGridBackend configuration');
        }
        if (array_key_exists($key, $curConfig) == false) {
            return null;
        }

        return $curConfig[$key];
    }

    /**
     * get helper for subgrid rendering
     * @param $subgridObj
     * @return AbstractHelper
     */
    protected function getSubgridHelper($subgridObj)
    {
        if (($config = $this->getConfigKey('subgridMap')) == false) {
            throw new Exception\InvalidArgumentException('missing "subgridMap" section in JqGridBackend configuration');
        }
        $subgridHelperClass = null;
        foreach ($config as $objClass => $helperClass) {
            if ($subgridObj instanceof $objClass) {
                $subgridHelperClass = $helperClass;
            }
        }
        if (! $subgridHelperClass ) {
            throw new Exception\InvalidArgumentException('need subgrid helper in "subgridMap" section in JqGridBackend configuration');
        }

        /** @var AbstractPluginManager $sm */
        $sm = $this->getPluginManager();
        /** @var AbstractHelper $subgridHelper */
        $subgridHelper = $sm->get($subgridHelperClass);
        return $subgridHelper;
    }

    /**
     * @return mixed
     */
    public function getColModelAdapterPluginManager()
    {
        return $this->colModelAdapterPluginManager;
    }

    /**
     * @param mixed $colModelAdapterPluginManager
     * @return self
     */
    public function setColModelAdapterPluginManager($colModelAdapterPluginManager)
    {
        $this->colModelAdapterPluginManager = $colModelAdapterPluginManager;
        return $this;
    }

    /**
     * @param FormElement $element
     * @return string
     * @thrown Exception\OutOfBoundsException
     */
    public function getAdapterName(FormElement $element)
    {
        $adapterName = null;
        foreach ($this->adapterMapConfig as $k => $v) {
            if (is_a($element, $k) == true) {
                $adapterName = $v;
            }
        }
        if (!$adapterName) {
            throw new Exception\OutOfBoundsException("missing ColModelAdapter for class = ". get_class($element));
        }
        return $adapterName;
    }

    /**
     * @return string
     */
    public function getConfigName()
    {
        return $this->configName;
    }

    /**
     * @param string $configName
     * @return self
     */
    public function setConfigName($configName)
    {
        $this->configName = $configName;
        return $this;
    }

    /**
     * akkumulate grid options in member gridOptions
     * @param GridObject $obj
     */
    private function setGridOptions(GridObject $obj)
    {
        $gridOptions = $this->getObjectGridOptions($obj);
        $configName = $this->getConfigName();
        $defaultOptions = $this->getDefaultOptions($obj, $configName);
        $this->gridOptions = array_merge($defaultOptions, $gridOptions);
    }

    /**
     * return merge the default and user options for grid
     * @return array
     */
    public function getGridOptions()
    {
        return $this->gridOptions;
    }

    private function setGridMethods(GridObject $obj)
    {
        $gridMethods = $this->getObjectGridMethods($obj);
        $configName = $this->getConfigName();
        $defaultMethods = $this->getDefaultMethods($obj, $configName);
        $this->gridMethods = array_merge($defaultMethods, $gridMethods);
    }

    public function getGridMethods()
    {
        return $this->gridMethods;
    }

    /**
     * @return GridObject
     */
    public function getObj()
    {
        return $this->obj;
    }

    /**
     * @param GridObject $obj
     * @return self
     */
    public function setObj($obj)
    {
        $this->obj = $obj;
        return $this;
    }

    /**
     * @return AbstractPluginManager
     */
    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    /**
     * @param AbstractPluginManager $pluginManager
     * @return self
     */
    public function setPluginManager($pluginManager)
    {
        $this->pluginManager = $pluginManager;
        return $this;
    }
}
