<?php
namespace App\Models\LottoModule;

class LottoFormula
{
    public static function __callStatic($name, $arguments)
    {
        $param   = $arguments[0];
        $basic28 = config('lotto.base.formula_basic28');

        if (in_array($name, $basic28)) {
            return self::basic28($param);
        }

        return false;
    }

    public static function basic11($open_code)
    {
        $source = explode(',', $open_code);
        $label  = [1, 4, 7, 10, 13, 16];
        $sec_1  = self::getCodeArea16($label, $source);
        $label  = [3, 6, 9, 12, 15, 18];
        $sec_2  = self::getCodeArea16($label, $source);

        $arrange = [$sec_1['mod'], $sec_2['mod']];
        $code_he = array_sum($arrange);
        $result  = [
            'code_arr' => $arrange,
            'code_he'  => $code_he,
            'code_str' => implode(',', $arrange),
            'source'   => $source,
            'extend'   => ['sec_1' => $sec_1, 'sec_2' => $sec_2],
        ];

        return $result;
    }

    public static function basic16($open_code)
    {
        $source = explode(',', $open_code);
        $label  = [1, 4, 7, 10, 13, 16];
        $sec_1  = self::getCodeArea16($label, $source);
        $label  = [2, 5, 8, 11, 14, 17];
        $sec_2  = self::getCodeArea16($label, $source);
        $label  = [3, 6, 9, 12, 15, 18];
        $sec_3  = self::getCodeArea16($label, $source);

        $arrange = [$sec_1['mod'], $sec_2['mod'], $sec_3['mod']];
        $code_he = array_sum($arrange);
        $result  = [
            'code_arr' => $arrange,
            'code_he'  => $code_he,
            'code_str' => implode(',', $arrange),
            'source'   => $source,
            'extend'   => ['sec_1' => $sec_1, 'sec_2' => $sec_2, 'sec_3' => $sec_3],
        ];

        return $result;
    }

    public static function basic28($open_code)
    {
        $source  = explode(',', $open_code);
        $label   = [2, 5, 8, 11, 14, 17];
        $sec_1   = self::getCodeArea28($label, $source);
        $label   = [3, 6, 9, 12, 15, 18];
        $sec_2   = self::getCodeArea28($label, $source);
        $label   = [4, 7, 10, 13, 16, 19];
        $sec_3   = self::getCodeArea28($label, $source);
        $arrange = [$sec_1['mod'], $sec_2['mod'], $sec_3['mod']];
        $code_he = array_sum($arrange);
        $result  = [
            'code_he'  => $code_he,
            'code_arr' => $arrange,
            'code_str' => implode(',', $arrange),
            'source'   => $source,
            'extend'   => ['sec_1' => $sec_1, 'sec_2' => $sec_2, 'sec_3' => $sec_3],
        ];

        return $result;
    }

    public static function bit28($source)
    {
        $hash     = hash('sha256', $source);
        $str_16   = substr($hash, 0, 16);
        $hex      = hexdec($str_16);
        $formula  = $hex / pow(2, 64);
        $open_str = substr($formula, 2, 3);
        $arrange  = str_split($open_str, 1);
        $code_he  = $arrange[0] + $arrange[1] + $arrange[2];
        $result   = [
            'code_he'  => $code_he,
            'source'   => $source,
            'code_arr' => $arrange,
            'code_str' => implode(',', $arrange),
        ];
        return $result;
    }

    public static function pc28($open_code)
    {
        $source = explode(',', $open_code);
        $label  = [1, 2, 3, 4, 5, 6];
        $sec_1  = self::getCodeArea28($label, $source);
        $label  = [7, 8, 9, 10, 11, 12];
        $sec_2  = self::getCodeArea28($label, $source);
        $label  = [13, 14, 15, 16, 17, 18];
        $sec_3  = self::getCodeArea28($label, $source);

        $arrange = [$sec_1['mod'], $sec_2['mod'], $sec_3['mod']];
        $code_he = array_sum($arrange);

        $result = [
            'code_he'  => $code_he,
            'code_arr' => $arrange,
            'code_str' => implode(',', $arrange),
            'source'   => $source,
            'extend'   => ['sec_1' => $sec_1, 'sec_2' => $sec_2, 'sec_3' => $sec_3],
        ];
        return $result;
    }

    private static function getCodeArea16($label, $source)
    {
        $result = ['code' => [], 'total' => 0, 'mod' => 0, 'label' => $label];
        foreach ($label as $value) {
            $code             = (int) $source[$value - 1];
            $result['code'][] = $code;
            $result['total'] += $code;
        }
        $temp          = $result['total'] % 6 + 1;
        $result['mod'] = (int) substr($temp, -1);
        return $result;
    }

    private static function getCodeArea28($label, $source)
    {
        $result = ['code' => [], 'total' => 0, 'mod' => 0, 'label' => $label];
        foreach ($label as $value) {
            $code             = (int) $source[$value - 1];
            $result['code'][] = $code;
            $result['total'] += $code;
        }
        $result['mod'] = (int) substr($result['total'], -1);
        return $result;
    }
}
