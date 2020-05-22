<?php
namespace App\Models\LottoModule;

class LottoWinPlace
{
    public static function baccarat($code)
    {
        $result                                    = [];
        $code % 2 == 0 && $result[]                = 'dob';
        $code % 2 == 1 && $result[]                = 'sig';
        $code >= 14 && $result[]                   = 'big';
        $code <= 13 && $result[]                   = 'sml';
        $code < 5 && $result[]                     = 'xsm';
        $code > 22 && $result[]                    = 'xbg';
        $code % 2 == 0 && $code >= 14 && $result[] = 'bdo';
        $code % 2 == 1 && $code >= 14 && $result[] = 'bsg';
        $code % 2 == 0 && $code <= 13 && $result[] = 'sdo';
        $code % 2 == 1 && $code <= 13 && $result[] = 'ssg';
        return $result;
    }

    public static function kuai3($code)
    {
        $code = explode(',', $code);
        $he   = $code[0] + $code[1] + $code[2];

        $result   = [];
        $result[] = 'he_' . sprintf('%02d', $he);

        $he % 2 == 0 && $result[] = 'he_dob';
        $he % 2 == 1 && $result[] = 'he_sig';
        $he >= 11 && $result[]    = 'he_big';
        $he <= 10 && $result[]    = 'he_sml';

        //是否为顺子
        asort($code);
        $str = implode('', $code);
        if (preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/', $str)) {
            $result[] = 'jun';
        }

        //是否为豹子
        $unique                           = array_unique($code);
        count($unique) === 1 && $result[] = 'leo';

        //三军
        foreach (array_unique($code) as $value) {
            $result[] = 'sj_' . $value;
        }

        return $result;
    }

    public static function lotto28($code)
    {
        $code = explode(',', $code);
        $he   = $code[0] + $code[1] + $code[2];
        $win  = [];

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

        $result = [];
        foreach ($win as $value) {
            $result[] = 'ba_' . $value;

            //外围玩法 13 14 组合吃掉
            // if (in_array($code, ['13', '14']) && !in_array($value, ['dob', 'sig', 'big', 'sml'])) {
            //     continue;
            // }

            $result[] = 'ww_' . $value;
        }
        $result[] = 'he_' . sprintf('%02d', $he);
        $result[] = 'fd_' . sprintf('%02d', $he);

        //胆拖
        foreach (array_unique($code) as $value) {
            $result[] = 'dt_' . $value;
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
            $prefix                                      = 'smp_' . sprintf('%02d', $position);
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
            $smp    = $smpFun($value, $key + 1, $result);
            $result = array_merge($result, $smp);
        }

        $he % 2 == 0 && $result[] = 'smp_he_dob';
        $he % 2 == 1 && $result[] = 'smp_he_sig';
        $he >= 23 && $result[]    = 'smp_he_big';
        $he <= 22 && $result[]    = 'smp_he_sml';

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
