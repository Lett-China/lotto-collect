<?php
namespace App\Models\LottoModule;

class LottoFormula
{
    public static function __callStatic($name, $arguments)
    {
        $param     = $arguments[0];
        $series_28 = ['bj28', 'ca28', 'de28', 'hero28', 'cw28'];

        if (in_array($name, $series_28)) {
            return self::basic28($param);
        }

        return false;
    }

    public static function basic28($open_code)
    {
        $source  = explode(',', $open_code);
        $label   = [2, 5, 8, 11, 14, 17];
        $sec_1   = self::getCodeArea($label, $source);
        $label   = [3, 6, 9, 12, 15, 18];
        $sec_2   = self::getCodeArea($label, $source);
        $label   = [4, 7, 10, 13, 16, 19];
        $sec_3   = self::getCodeArea($label, $source);
        $value   = $sec_1['mantissa'] + $sec_2['mantissa'] + $sec_3['mantissa'];
        $arrange = [
            $sec_1['mantissa'],
            $sec_2['mantissa'],
            $sec_3['mantissa'],
        ];
        $result = [
            'code_he'  => $value,
            'extend'   => [
                'sec_1' => $sec_1,
                'sec_2' => $sec_2,
                'sec_3' => $sec_3,
            ],
            'code_arr' => $arrange,
            'code_str' => implode(',', $arrange),
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
        $source  = explode(',', $open_code);
        $label   = [1, 2, 3, 4, 5, 6];
        $sec_1   = self::getCodeArea($label, $source);
        $label   = [7, 8, 9, 10, 11, 12];
        $sec_2   = self::getCodeArea($label, $source);
        $label   = [13, 14, 15, 16, 17, 18];
        $sec_3   = self::getCodeArea($label, $source);
        $value   = $sec_1['mantissa'] + $sec_2['mantissa'] + $sec_3['mantissa'];
        $arrange = [
            $sec_1['mantissa'],
            $sec_2['mantissa'],
            $sec_3['mantissa'],
        ];
        $result = [
            'code_he'  => $value,
            'extend'   => [
                'sec_1' => $sec_1,
                'sec_2' => $sec_2,
                'sec_3' => $sec_3,
            ],
            'code_arr' => $arrange,
            'code_str' => implode(',', $arrange),
        ];
        return $result;
    }

    private static function getCodeArea($label, $source)
    {
        $result = ['code' => [], 'total' => 0, 'mantissa' => 0];
        foreach ($label as $value) {
            $code             = (int) $source[$value - 1];
            $result['code'][] = $code;
            $result['total'] += $code;
        }
        $result['mantissa'] = (int) substr($result['total'], -1);
        return $result;
    }
}
