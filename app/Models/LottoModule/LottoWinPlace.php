<?php
namespace App\Models\LottoModule;

class LottoWinPlace
{
    public static function kuai3($code)
    {
        $code = explode(',', $code);
        $he   = $code[0] + $code[1] + $code[2];

        $result   = [];
        $result[] = 'qth_' . sprintf('%02d', $he);

        $he % 2 == 0 && $result[] = 'qth_dob';
        $he % 2 == 1 && $result[] = 'qth_sig';
        $he >= 11 && $result[]    = 'qth_big';
        $he <= 10 && $result[]    = 'qth_sml';

        //是否为顺子
        asort($code);
        $str = implode('', $code);
        if (preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/', $str)) {
            $result[] = 'jun';
        }

        //是否为豹子
        $unique                           = array_unique($code);
        count($unique) === 1 && $result[] = 'qto_leo';

        //三军
        foreach (array_unique($code) as $value) {
            $result[] = 'qtj_' . $value;
        }

        return $result;
    }

    public static function lotto28($open_code, $lotto_name)
    {
        $formula = LottoFormula::$lotto_name($open_code);

        $he  = $formula['code_he'];
        $win = [];

        $he % 2 == 0 && $win[]              = 'dob';
        $he % 2 == 1 && $win[]              = 'sig';
        $he >= 14 && $win[]                 = 'big';
        $he <= 13 && $win[]                 = 'sml';
        $he < 5 && $win[]                   = 'xsm';
        $he > 22 && $win[]                  = 'xbg';
        $he % 2 == 0 && $he >= 14 && $win[] = 'bdo';
        $he % 2 == 1 && $he >= 14 && $win[] = 'bsg';
        $he % 2 == 0 && $he <= 13 && $win[] = 'sdo';
        $he % 2 == 1 && $he <= 13 && $win[] = 'ssg';

        //36部分开奖
        $code = $formula['code_arr'];
        asort($code);
        $code   = array_values($code);
        $str    = implode('', $code);
        $unique = array_unique($code);
        $win_ts = 'oth'; //36玩法开奖 默认为杂

        ($code[0] + 1 == $code[1] || $code[1] + 1 == $code[2] || ($code[0] == 0 && $code[2] == 9)) && $win_ts = 'juh'; //半顺
        count($unique) === 2 && $win_ts                                                                       = 'pai'; //对
        count($unique) === 1 && $win_ts                                                                       = 'leo'; //豹子
        implode('', $code) === '019' && $win_ts                                                               = 'jun'; //019为顺
        implode('', $code) === '089' && $win_ts                                                               = 'jun'; //019为顺
        ($code[0] + 1 == $code[1] && $code[1] + 1 == $code[2]) && $win_ts                                     = 'jun'; //顺

        $result   = [];
        $result[] = 'ts_' . $win_ts;

        foreach ($win as $value) {
            $result[] = 'ww_' . $value;
        }

        $result[] = 'he_' . sprintf('%02d', $he);
        $result[] = 'fd_' . sprintf('%02d', $he);

        //16玩法
        $has_ext = config('lotto.base')['win_place_has_st'];
        if (in_array($lotto_name, $has_ext)) {
            $formula  = LottoFormula::basic16($open_code);
            $result[] = 'st_' . sprintf('%02d', $formula['code_he']);
            $formula  = LottoFormula::basic11($open_code);
            $result[] = 'el_' . sprintf('%02d', $formula['code_he']);
        }

        return $result;
    }

    public static function racing($code)
    {
        $code   = explode(',', $code);
        $result = [];

        //冠亚和
        $gyh                       = sprintf('%02d', $code[0] + $code[1]);
        $gyh % 2 == 0 && $result[] = 'gyh_dob';
        $gyh % 2 == 1 && $result[] = 'gyh_sig';
        $gyh >= 12 && $result[]    = 'gyh_big';
        $gyh <= 11 && $result[]    = 'gyh_sml';
        $result[]                  = 'gyh_' . $gyh;

        for ($i = 1; $i <= 10; $i++) {
            $win_prefix                 = 'smp_' . sprintf('%02d', $i) . '_';
            $temp                       = $code[$i - 1];
            $temp % 2 == 0 && $result[] = $win_prefix . 'dob';
            $temp % 2 == 1 && $result[] = $win_prefix . 'sig';
            $temp >= 6 && $result[]     = $win_prefix . 'big';
            $temp <= 5 && $result[]     = $win_prefix . 'sml';

            if ($i <= 5) {
                $temp_1   = $code[$i - 1];
                $position = 11 - $i;
                $temp_2   = $code[10 - $i];
                if ($temp_1 > $temp_2) {
                    $result[] = $win_prefix . 'drg';
                } else {
                    $result[] = $win_prefix . 'tig';
                }
            }

            //计算定位胆
            $result[] = 'dwd_' . sprintf('%02d', $i) . '_' . sprintf('%02d', $temp);
        }

        return $result;
    }

    public static function shishicai($code)
    {
        $code   = explode(',', $code);
        $result = [];

        //双面盘算法
        $smpFun = function ($num, $position) {
            $prefix                                      = 'ssm_' . sprintf('%02d', $position);
            $result                                      = [];
            $num % 2 == 0 && $result[]                   = $prefix . '_dob';
            $num % 2 == 1 && $result[]                   = $prefix . '_sig';
            $num >= 5 && $result[]                       = $prefix . '_big';
            $num <= 4 && $result[]                       = $prefix . '_sml';
            in_array($num, [1, 2, 3, 5, 7]) && $result[] = $prefix . '_qua';
            in_array($num, [0, 4, 6, 8, 9]) && $result[] = $prefix . '_clo';
            return $result;
        };

        //龙虎斗
        $lhdFun = function ($position, $num1, $num2) {
            $prefix                   = 'lhd_' . $position;
            $result                   = '';
            $num1 > $num2 && $result  = $prefix . '_drg';
            $num1 < $num2 && $result  = $prefix . '_tig';
            $num1 == $num2 && $result = $prefix . '_pea';
            return $result;
        };

        //和值
        $he = 0;
        foreach ($code as $key => $value) {
            $he += $value;
            $smp    = $smpFun($value, $key + 1);
            $result = array_merge($result, $smp);

            //定位胆
            $result[] = 'sdw_' . sprintf('%02d', $key + 1) . '_' . $value;
        }

        $he % 2 == 0 && $result[] = 'ssm_he_dob';
        $he % 2 == 1 && $result[] = 'ssm_he_sig';
        $he >= 23 && $result[]    = 'ssm_he_big';
        $he <= 22 && $result[]    = 'ssm_he_sml';

        //龙虎斗
        $result[] = $lhdFun('wq', $code[0], $code[1]);
        $result[] = $lhdFun('wb', $code[0], $code[2]);
        $result[] = $lhdFun('ws', $code[0], $code[3]);
        $result[] = $lhdFun('wg', $code[0], $code[4]);
        $result[] = $lhdFun('qb', $code[1], $code[2]);
        $result[] = $lhdFun('qs', $code[1], $code[3]);
        $result[] = $lhdFun('qg', $code[1], $code[4]);
        $result[] = $lhdFun('bs', $code[2], $code[3]);
        $result[] = $lhdFun('bg', $code[2], $code[3]);
        $result[] = $lhdFun('sg', $code[3], $code[4]);

        return $result;
    }
}
