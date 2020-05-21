<?php
return [
    'group' => [
        ['title' => '28系列', 'name' => 'series_28'],
        ['title' => '快乐彩', 'name' => 'series_kuaile'],
        ['title' => '快三', 'name' => 'series_kuai3'],
        ['title' => '时时彩', 'name' => 'shishicai'],
    ],
    'items' => [
        [
            'name'  => '英雄28',
            'intro' => '每90秒一期',
            'icon'  => 'hero28',
            'route' => [
                'name'   => 'lotto28',
                'params' => [
                    'name' => 'hero28',
                ],
            ],
            'group' => 'series_28',
            'hot'   => true,
        ],

        // [
        //     'name'  => '比特币28',
        //     'intro' => '每60秒一期',
        //     'icon'  => 'hero28',
        //     'route' => [
        //         'name'   => 'lotto28',
        //         'params' => [
        //             'name' => 'bit28',
        //         ],
        //     ],
        //     'group' => 'series_28',
        //     'hot'   => true,
        // ],
        // [
        //     'name'  => '德国28',
        //     'intro' => '每90秒一期',
        //     'icon'  => 'hero28',
        //     'route' => [
        //         'name'   => 'lotto28',
        //         'params' => [
        //             'name' => 'de28',
        //         ],
        //     ],
        //     'group' => 'series_28',
        //     'hot'   => true,
        // ],
        [
            'name'  => '加拿大28',
            'intro' => '每210秒一期',
            'icon'  => 'canada28',
            'route' => [
                'name'   => 'lotto28',
                'params' => [
                    'name' => 'ca28',
                ],
            ],
            'group' => 'series_28',
            'hot'   => true,
        ],
        [
            'name'  => '北京28',
            'intro' => '每5分钟一期',
            'icon'  => 'beijing28',
            'route' => [
                'name'   => 'lotto28',
                'params' => [
                    'name' => 'bj28',
                ],
            ],
            'group' => 'series_28',
        ],
        [
            'name'  => '蛋蛋28',
            'intro' => '每5分钟一期',
            'icon'  => 'dd28',
            'route' => [
                'name'   => 'lotto28',
                'params' => [
                    'name' => 'pc28',
                ],
            ],
            'group' => 'series_28',
        ],
        [
            'name'  => '北京PK10',
            'intro' => '每20分钟一期',
            'icon'  => 'pk10',
            'route' => [
                'name'   => 'lottoRacing',
                'params' => [
                    'name' => 'pk10',
                ],
            ],
            'group' => 'series_kuaile',
        ],
        [
            'name'  => '幸运飞艇',
            'intro' => '每5分钟一期',
            'icon'  => 'mlaft',
            'route' => [
                'name'   => 'lottoRacing',
                'params' => [
                    'name' => 'mlaft',
                ],
            ],
            'group' => 'series_kuaile',
            'hot'   => true,
        ],
        [
            'name'  => '北京快三',
            'intro' => '每天44期',
            'icon'  => 'kuai3',
            'route' => [
                'name'   => 'lottoKuai3',
                'params' => [
                    'name' => 'bjk3',
                ],
            ],
            'group' => 'series_kuai3',
        ],
        [
            'name'  => '安徽快三',
            'intro' => '每天40期',
            'icon'  => 'kuai3',
            'route' => [
                'name'   => 'lottoKuai3',
                'params' => [
                    'name' => 'ahk3',
                ],
            ],
            'group' => 'series_kuai3',
        ],
        [
            'name'  => '江苏快三',
            'intro' => '每天41期',
            'icon'  => 'kuai3',
            'route' => [
                'name'   => 'lottoKuai3',
                'params' => [
                    'name' => 'jsk3',
                ],
            ],
            'group' => 'series_kuai3',
        ],
        [
            'name'  => '上海快三',
            'intro' => '每天41期',
            'icon'  => 'kuai3',
            'route' => [
                'name'   => 'lottoKuai3',
                'params' => [
                    'name' => 'shk3',
                ],
            ],
            'group' => 'series_kuai3',
        ],
        [
            'name'  => '河北快三',
            'intro' => '每天41期',
            'icon'  => 'kuai3',
            'route' => [
                'name'   => 'lottoKuai3',
                'params' => [
                    'name' => 'hebk3',
                ],
            ],
            'group' => 'series_kuai3',
        ],
        [
            'name'  => '湖北快三',
            'intro' => '每天39期',
            'icon'  => 'kuai3',
            'route' => [
                'name'   => 'lottoKuai3',
                'params' => [
                    'name' => 'hubk3',
                ],
            ],
            'group' => 'series_kuai3',
        ],

        [
            'name'  => '新疆时时彩',
            'intro' => '每天48期',
            'icon'  => 'shishicai',
            'route' => [
                'name'   => 'lottoShiShiCai',
                'params' => [
                    'name' => 'xjssc',
                ],
            ],
            'group' => 'shishicai',
        ],

        [
            'name'  => '天津时时彩',
            'intro' => '每天42期',
            'icon'  => 'shishicai',
            'route' => [
                'name'   => 'lottoShiShiCai',
                'params' => [
                    'name' => 'tjssc',
                ],
            ],
            'group' => 'shishicai',
        ],

        [
            'name'  => '黑龙江时时彩',
            'intro' => '每天42期',
            'icon'  => 'shishicai',
            'route' => [
                'name'   => 'lottoShiShiCai',
                'params' => [
                    'name' => 'hljssc',
                ],
            ],
            'group' => 'shishicai',
        ],

    ],
];
