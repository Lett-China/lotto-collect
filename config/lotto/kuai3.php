<?php
$result = [
    'win_function' => 'kuai3',
    'mock'         => ['place' => []],
    'bet_places'   => [
        [
            '_name'  => '大',
            'odds'   => '1.95',
            'place'  => 'he_big',
            'group'  => 'he',
            'name'   => '和-大',
            'regexp' => ['大'],
        ],
        [
            '_name'  => '小',
            'odds'   => '1.95',
            'place'  => 'he_sml',
            'group'  => 'he',
            'name'   => '和-小',
            'regexp' => ['小'],
        ],
        [
            '_name'  => '单',
            'odds'   => '1.95',
            'place'  => 'he_sig',
            'group'  => 'he',
            'name'   => '和-单',
            'regexp' => ['单'],
        ],
        [
            '_name'  => '双',
            'odds'   => '1.95',
            'place'  => 'he_dob',
            'group'  => 'he',
            'name'   => '和-双',
            'regexp' => ['双'],
        ],
        [
            '_name'  => '03',
            'odds'   => '189.00',
            'place'  => 'he_03',
            'group'  => 'he',
            'name'   => '和-03',
            'regexp' => ['3', '03'],
        ],
        [
            '_name'  => '04',
            'odds'   => '63.00',
            'place'  => 'he_04',
            'group'  => 'he',
            'name'   => '和-04',
            'regexp' => ['4', '04'],
        ],
        [
            '_name'  => '05',
            'odds'   => '31.50',
            'place'  => 'he_05',
            'group'  => 'he',
            'name'   => '和-05',
            'regexp' => ['5', '05'],
        ],
        [
            '_name'  => '06',
            'odds'   => '18.90',
            'place'  => 'he_06',
            'group'  => 'he',
            'name'   => '和-06',
            'regexp' => ['6', '06'],
        ],
        [
            '_name'  => '07',
            'odds'   => '12.60',
            'place'  => 'he_07',
            'group'  => 'he',
            'name'   => '和-07',
            'regexp' => ['7', '07'],
        ],
        [
            '_name'  => '08',
            'odds'   => '9.00',
            'place'  => 'he_08',
            'group'  => 'he',
            'name'   => '和-08',
            'regexp' => ['8', '08'],
        ],
        [
            '_name'  => '09',
            'odds'   => '7.56',
            'place'  => 'he_09',
            'group'  => 'he',
            'name'   => '和-09',
            'regexp' => ['9', '09'],
        ],
        [
            '_name'  => '10',
            'odds'   => '7.00',
            'place'  => 'he_10',
            'group'  => 'he',
            'name'   => '和-10',
            'regexp' => ['10'],
        ],
        [
            '_name'  => '11',
            'odds'   => '7.00',
            'place'  => 'he_11',
            'group'  => 'he',
            'name'   => '和-11',
            'regexp' => ['11'],
        ],
        [
            '_name'  => '12',
            'odds'   => '7.56',
            'place'  => 'he_12',
            'group'  => 'he',
            'name'   => '和-12',
            'regexp' => ['12'],
        ],
        [
            '_name'  => '13',
            'odds'   => '9.00',
            'place'  => 'he_13',
            'group'  => 'he',
            'name'   => '和-13',
            'regexp' => ['13'],
        ],
        [
            '_name'  => '14',
            'odds'   => '12.60',
            'place'  => 'he_14',
            'group'  => 'he',
            'name'   => '和-14',
            'regexp' => ['14'],
        ],
        [
            '_name'  => '15',
            'odds'   => '18.90',
            'place'  => 'he_15',
            'group'  => 'he',
            'name'   => '和-15',
            'regexp' => ['15'],
        ],
        [
            '_name'  => '16',
            'odds'   => '31.50',
            'place'  => 'he_16',
            'group'  => 'he',
            'name'   => '和-16',
            'regexp' => ['16'],
        ],
        [
            '_name'  => '17',
            'odds'   => '63.00',
            'place'  => 'he_17',
            'group'  => 'he',
            'name'   => '和-17',
            'regexp' => ['17'],
        ],
        [
            '_name'  => '18',
            'odds'   => '189.00',
            'place'  => 'he_18',
            'group'  => 'he',
            'name'   => '和-18',
            'regexp' => ['18'],
        ],

        //其它玩法

        [
            '_name'  => '豹子通选',
            'odds'   => '31.00',
            'place'  => 'leo',
            'group'  => 'other',
            'name'   => '豹子通选',
            'regexp' => ['豹子'],
        ],
        [
            '_name'  => '顺子通选',
            'odds'   => '7.87',
            'place'  => 'jun',
            'group'  => 'other',
            'name'   => '顺子通选',
            'regexp' => ['顺子'],
        ],

    ],
];

for ($i = 1; $i <= 6; $i++) {
    $result['bet_places'][] = [
        '_name' => sprintf('%02d', $i),
        'odds'  => '1.960',
        'place' => 'sj_' . $i,
        'group' => 'sj',
        'name'  => '三军-' . $i,
    ];
}

foreach ($result['bet_places'] as $value) {
    $value['odds'] <= 10 && $result['mock']['place'][] = $value['place'];
}

return $result;
