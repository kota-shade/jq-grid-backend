<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 10.02.17
 * Time: 18:24
 */

return [
    'assetic_configuration' => [
        'modules' => [
            'jq-grid-backend' => [
                'root_path' => __DIR__ . '/../assets',
                'collections' => [
                    'jq_grid_common_js' => ['assets' => [
                        'js/common.js'
                    ]],
                    'jq_grid_common_css' => ['assets' => [
                        'css/common.css'
                    ]],
                ],
            ],
        ],
    ]
];

