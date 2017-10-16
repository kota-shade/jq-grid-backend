<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 15.02.17
 * Time: 13:58
 */
use JqGridBackend\Factory;

return [
    'grid_adapter_manager' => [
        'aliases' => [],
        'abstract_factories' => [
            /**
             * объявить в приложении при необходимости
             */
            //Factory\AbstractJqGridDbalAdapterFactory::class
        ],
        'factories' => []
    ]
];