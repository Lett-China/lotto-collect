<?php
namespace App\Packages\Utils;

class SMS
{
    public static function check($prefix, $mobile, $ver_code)
    {
        if ($ver_code == config('sms.un_check_code')) {
            return true;
        }
        $key = $prefix . ':' . $mobile;

        $temp = cache()->get($key);
        if (!$temp || $ver_code != $temp['ver_code']) {
            return false;
        }
        cache()->forget($key);

        return true;
    }

    public static function send($prefix, $mobile)
    {
        $code = mt_rand(100000, 999999);
        $key  = $prefix . ':' . $mobile;
        $data = ['ver_code' => $code];
        cache()->put($key, $data, 60000);

        $params = [
            'code' => $code,
            // 'product' => config('sms.ali.product_name'),
        ];
        $sms = self::AliSMS('SMS_182669091', $mobile, $params);

        return ['success' => $sms];
    }

    private static function AliSMS($temp_code, $phone, $temp_param = null)
    {
        $app_key    = config('sms.ali.access_key_id');
        $app_secret = config('sms.ali.access_key_secret');
        $sign_name  = config('sms.ali.sign_name');

        \AlibabaCloud\Client\AlibabaCloud::accessKeyClient($app_key, $app_secret)
            ->regionId('cn-hangzhou')
            ->asGlobalClient();

        try {
            $temp_param = json_encode($temp_param, JSON_UNESCAPED_UNICODE);
            \AlibabaCloud\Client\AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers'  => $phone,
                        'SignName'      => $sign_name,
                        'TemplateCode'  => $temp_code,
                        'TemplateParam' => $temp_param,
                    ],
                ])
                ->request();
            return true;
        } catch (\AlibabaCloud\Client\Exception\ClientException $error) {
            return false;
        } catch (\AlibabaCloud\Client\Exception\ServerException $error) {
            return false;
        }

        return false;
    }
}
