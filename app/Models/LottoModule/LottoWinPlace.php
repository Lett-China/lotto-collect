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
        //和组合开奖
        $he_ass_fun = function ($he) {
            $result = [];
            //和值组合开奖
            $he % 2 == 0 && $result[]              = 'dob';
            $he % 2 == 1 && $result[]              = 'sig';
            $he >= 14 && $result[]                 = 'big';
            $he <= 13 && $result[]                 = 'sml';
            $he < 5 && $result[]                   = 'xsm';
            $he > 22 && $result[]                  = 'xbg';
            $he % 2 == 0 && $he >= 14 && $result[] = 'bdo';
            $he % 2 == 1 && $he >= 14 && $result[] = 'bsg';
            $he % 2 == 0 && $he <= 13 && $result[] = 'sdo';
            $he % 2 == 1 && $he <= 13 && $result[] = 'ssg';

            return $result;
        };

        //特殊玩法开奖
        $spe_fun = function ($code) {
            asort($code);
            $code   = array_values($code);
            $str    = implode('', $code);
            $unique = array_unique($code);
            $result = 'oth'; //36玩法开奖 默认为杂

            ($code[0] + 1 == $code[1] || $code[1] + 1 == $code[2] || ($code[0] == 0 && $code[2] == 9)) && $result = 'juh'; //半顺
            count($unique) === 2 && $result                                                                       = 'pai'; //对
            count($unique) === 1 && $result                                                                       = 'leo'; //豹子
            implode('', $code) === '019' && $result                                                               = 'jun'; //019为顺
            implode('', $code) === '089' && $result                                                               = 'jun'; //890为顺
            ($code[0] + 1 == $code[1] && $code[1] + 1 == $code[2]) && $result                                     = 'jun'; //顺
            return $result;
        };

        //外围群玩法
        $qq_win_place = function () use ($open_code, $lotto_name, $he_ass_fun, $spe_fun) {
            $formula = LottoFormula::basicQq($open_code, $lotto_name);
            $result  = [];

            $he   = $formula['code_he'];
            $code = $formula['code_arr'];

            $result[] = 'qua_' . sprintf('%02d', $he);
            $result[] = 'qub_' . sprintf('%02d', $he);

            $he_ass = $he_ass_fun($he);
            foreach ($he_ass as $value) {
                $result[] = 'qua_' . $value;
                $result[] = 'qub_' . $value;
            }

            //特殊玩法
            $spe = $spe_fun($code);
            if (in_array($spe, ['pai', 'leo', 'jun'])) {
                $result[] = 'qua_' . $spe;
                $result[] = 'qub_' . $spe;
            }

            //定位胆开奖
            $dwd = [];
            foreach ($code as $index => $value) {
                $prefix                   = 'q' . ($index + 1) . '_';
                $dwd[]                    = $prefix . sprintf('%02d', $value);
                $value % 2 == 0 && $dwd[] = $prefix . 'dob';
                $value % 2 == 1 && $dwd[] = $prefix . 'sig';
                $value >= 5 && $dwd[]     = $prefix . 'big';
                $value <= 4 && $dwd[]     = $prefix . 'sml';
            }

            $code[0] > $code[2] && $dwd[]  = 'qq_drg';
            $code[0] < $code[2] && $dwd[]  = 'qq_tig';
            $code[0] == $code[2] && $dwd[] = 'qq_pea';

            //色波开奖
            in_array($he, ['01', '02', '07', '08', '12', '13', '18', '19', '23', '24']) && $dwd[] = 'red';
            in_array($he, ['03', '04', '09', '10', '14', '15', '20', '25', '26']) && $dwd[]       = 'blue';
            in_array($he, ['00', '05', '06', '11', '16', '17', '21', '22', '27']) && $dwd[]       = 'green';

            foreach (['qua', 'qub'] as $room) {
                foreach ($dwd as $place) {
                    $result[] = $room . '_' . $place;
                }
            }

            return $result;
        };

        $formula = LottoFormula::$lotto_name($open_code);
        $result  = [];

        $he   = $formula['code_he'];
        $code = $formula['code_arr'];

        //和值开奖
        $result[] = 'he_' . sprintf('%02d', $he);
        $result[] = 'fd_' . sprintf('%02d', $he);

        //外围开奖
        foreach ($he_ass_fun($he) as $value) {
            $result[] = 'ww_' . $value;
        }

        //36开奖
        $ts_win   = $spe_fun($code);
        $result[] = 'ts_' . $ts_win;

        //16/11玩法
        if ($lotto_name !== 'bit28') {
            $formula  = LottoFormula::basic16($open_code);
            $result[] = 'st_' . sprintf('%02d', $formula['code_he']);
            $formula  = LottoFormula::basic11($open_code);
            $result[] = 'el_' . sprintf('%02d', $formula['code_he']);
        }

        //组合群玩法开奖
        $qq_win = $qq_win_place();
        $result = array_merge($result, $qq_win);

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
