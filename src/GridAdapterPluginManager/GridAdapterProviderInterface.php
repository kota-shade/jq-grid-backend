<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 25.07.16
 * Time: 22:33
 */
namespace JqGridBackend\GridAdapterPluginManager;

interface GridAdapterProviderInterface {
    /**
     * @return array|mixed
     */
    public function getGridAdapterConfig();
}