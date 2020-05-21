<?php
$result = [
    'win_function' => 'lotto28',
    'mock'         => [
        'place' => ['ba_big', 'ba_sml', 'ba_sig', 'ba_dob', 'ba_bdo', 'ba_bsg', 'ba_sdo', 'ba_bsg', 'ww_big', 'ww_sml', 'ww_sig', 'ww_dob', 'ww_bdo', 'ww_bsg', 'ww_sdo', 'ww_bsg', 'he_10', 'he_11', 'he_12', 'he_13', 'he_14', 'he_15', 'he_17'],
    ],
    'bet_places'   => [
        [
            '_name'  => '大',
            'name'   => '百家乐-大',
            'odds'   => '1.970',
            'place'  => 'ba_big',
            'group'  => 'baccarat',
            'regexp' => ['大', '百家乐大', '百家乐-大'],
            'hot'    => true,
        ],
        [
            '_name'  => '小',
            'name'   => '百家乐-小',
            'odds'   => '1.970',
            'place'  => 'ba_sml',
            'group'  => 'baccarat',
            'regexp' => ['小', '百家乐小', '百家乐-小'],
            'hot'    => true,
        ],
        [
            '_name'  => '单',
            'name'   => '百家乐-单',
            'odds'   => '1.970',
            'place'  => 'ba_sig',
            'group'  => 'baccarat',
            'regexp' => ['单', '百家乐单', '百家乐-单'],
            'hot'    => true,
        ],
        [
            '_name'  => '双',
            'name'   => '百家乐-双',
            'odds'   => '1.970',
            'place'  => 'ba_dob',
            'group'  => 'baccarat',
            'regexp' => ['双', '百家乐双', '百家乐-双'],
            'hot'    => true,
        ],
        [
            '_name'  => '大双',
            'name'   => '百家乐-大双',
            'odds'   => '3.530',
            'place'  => 'ba_bdo',
            'group'  => 'baccarat',
            'regexp' => ['大双', '百家乐大双', '百家乐-大双'],
        ],
        [
            '_name'  => '大单',
            'name'   => '百家乐-大单',
            'odds'   => '4.110',
            'place'  => 'ba_bsg',
            'group'  => 'baccarat',
            'regexp' => ['大单', '百家乐大单', '百家乐-大单'],
        ],
        [
            '_name'  => '小双',
            'name'   => '百家乐-小双',
            'odds'   => '4.110',
            'place'  => 'ba_sdo',
            'group'  => 'baccarat',
            'regexp' => ['小双', '百家乐小双', '百家乐-小双'],
        ],
        [
            '_name'  => '小单',
            'name'   => '百家乐-小单',
            'odds'   => '3.530',
            'place'  => 'ba_ssg',
            'group'  => 'baccarat',
            'regexp' => ['小单', '百家乐小单', '百家乐-小单'],
        ],
        [
            '_name'  => '极大',
            'name'   => '百家乐-极大',
            'odds'   => '12.000',
            'place'  => 'ba_xbg',
            'group'  => 'baccarat',
            'regexp' => ['极大', '百家乐极大', '百家乐-极大'],
        ],
        [
            '_name'  => '极小',
            'name'   => '百家乐-极小',
            'odds'   => '12.000',
            'place'  => 'ba_xsm',
            'group'  => 'baccarat',
            'regexp' => ['极小', '百家乐极小', '百家乐-极小'],
        ],

        //外围

        [
            '_name'  => '大',
            'name'   => '外围-大',
            'odds'   => '2.100',
            'place'  => 'ww_big',
            'group'  => 'waiwei',
            'regexp' => ['外围大', '外围-大'],
            'hot'    => true,
        ],
        [
            '_name'  => '小',
            'name'   => '外围-小',
            'odds'   => '2.100',
            'place'  => 'ww_sml',
            'group'  => 'waiwei',
            'regexp' => ['外围小', '外围-小'],
            'hot'    => true,
        ],
        [
            '_name'  => '单',
            'name'   => '外围-单',
            'odds'   => '2.100',
            'place'  => 'ww_sig',
            'group'  => 'waiwei',
            'regexp' => ['外围单', '外围-单'],
            'hot'    => true,
        ],
        [
            '_name'  => '双',
            'name'   => '外围-双',
            'odds'   => '2.100',
            'place'  => 'ww_dob',
            'group'  => 'waiwei',
            'regexp' => ['外围双', '外围-双'],
            'hot'    => true,
        ],
        [
            '_name'  => '大双',
            'name'   => '外围-大双',
            'odds'   => '4.650',
            'place'  => 'ww_bdo',
            'group'  => 'waiwei',
            'regexp' => ['外围大双', '外围-大双'],
        ],
        [
            '_name'  => '大单',
            'name'   => '外围-大单',
            'odds'   => '4.260',
            'place'  => 'ww_bsg',
            'group'  => 'waiwei',
            'regexp' => ['外围大单', '外围-大单'],
        ],
        [
            '_name'  => '小双',
            'name'   => '外围-小双',
            'odds'   => '4.260',
            'place'  => 'ww_sdo',
            'group'  => 'waiwei',
            'regexp' => ['外围小双', '外围-小双'],
        ],
        [
            '_name'  => '小单',
            'name'   => '外围-小单',
            'odds'   => '4.650',
            'place'  => 'ww_ssg',
            'group'  => 'waiwei',
            'regexp' => ['外围小单', '外围-小单'],
        ],
        [
            '_name'  => '极大',
            'name'   => '外围-极大',
            'odds'   => '17.000',
            'place'  => 'ww_xbg',
            'group'  => 'waiwei',
            'regexp' => ['外围极大', '外围-极大'],
        ],
        [
            '_name'  => '极小',
            'name'   => '外围-极小',
            'odds'   => '17.000',
            'place'  => 'ww_xsm',
            'group'  => 'waiwei',
            'regexp' => ['外围极小', '外围-极小'],
        ],
    ],
];

$he_rate = [1, 3, 6, 10, 15, 21, 28, 36, 45, 55, 63, 69, 73, 75, 75, 73, 69, 63, 55, 45, 36, 28, 21, 15, 10, 6, 3, 1];
$he_odds = [];
for ($i = 0; $i <= 27; $i++) {
    $odds      = 1000 / $he_rate[$i] * 0.98;
    $odds      = intval($odds * 1000) / 1000;
    $he_odds[] = sprintf('%.2f', $odds) . '0';
}

for ($i = 0; $i <= 27; $i++) {
    $name                   = sprintf('%02d', $i);
    $result['bet_places'][] = [
        '_name'  => $name,
        'name'   => '和-' . $name,
        'odds'   => $he_odds[$i],
        'place'  => 'he_' . $name,
        'group'  => 'he',
        'regexp' => [$name, '和' . $name, '和-' . $name],
    ];
}

for ($i = 0; $i <= 9; $i++) {
    $name                   = sprintf('%01d', $i);
    $result['bet_places'][] = [
        // '_name'  => '胆拖-',
        '_name'  => $name,
        'name'   => '胆拖-' . $name,
        'odds'   => '3.260',
        'place'  => 'dt_' . $name,
        'group'  => 'dt',
        'regexp' => ['胆拖' . $name, '胆拖-' . $name],
    ];
}

return $result;
