<?php

$system = [
    'hero28'  => 'App\Models\LottoModule\Models\LottoKenoHero',
    'ca28'    => 'App\Models\LottoModule\Models\LottoKenoCa',
    'cw28'    => 'App\Models\LottoModule\Models\LottoKenoCw',
    'keno-cw' => 'App\Models\LottoModule\Models\LottoKenoCw',
    'de28'    => 'App\Models\LottoModule\Models\LottoKenoDe',
    'keno-de' => 'App\Models\LottoModule\Models\LottoKenoDe',
    'bit28'   => 'App\Models\LottoModule\Models\LottoBit28',
    'bj28'    => 'App\Models\LottoModule\Models\LottoBeiJing8',
    'pc28'    => 'App\Models\LottoModule\Models\LottoPc28',
    'mlaft'   => 'App\Models\LottoModule\Models\LottoMlaft',
    'pk10'    => 'App\Models\LottoModule\Models\LottoPK10',
    'bjk3'    => 'App\Models\LottoModule\Models\LottoK3BeiJing',
    'ahk3'    => 'App\Models\LottoModule\Models\LottoK3AnHui',
    'jsk3'    => 'App\Models\LottoModule\Models\LottoK3JiangSu',
    'shk3'    => 'App\Models\LottoModule\Models\LottoK3ShangHai',
    'hebk3'   => 'App\Models\LottoModule\Models\LottoK3HeBei',
    'hubk3'   => 'App\Models\LottoModule\Models\LottoK3HuBei',
    'xjssc'   => 'App\Models\LottoModule\Models\LottoSscXinJiang',
    'tjssc'   => 'App\Models\LottoModule\Models\LottoSscTianJin',
    'hljssc'  => 'App\Models\LottoModule\Models\LottoSscHeiLongJiang',
];

//$collect为对应采集model
$collect = [
    'cakeno' => 'App\Models\LottoModule\Models\LottoKenoCa',
    'bjkl8'  => 'App\Models\LottoModule\Models\LottoBeiJing8',
    'bjpk10' => 'App\Models\LottoModule\Models\LottoPK10',
];
$collect = array_merge($collect, $system);
unset($collect['ca28'], $collect['bj28'], $collect['pc28'], $collect['pk10']);

return [
    'collect' => $collect,
    'system'  => $system,
];
