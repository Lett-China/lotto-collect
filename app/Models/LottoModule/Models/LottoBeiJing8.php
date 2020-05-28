<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoBeiJing8 extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_beijing8';

    protected $configs = [
        'next_second'  => 300,
        'first_second' => 33000,
        'last_time'    => '23:55:00',
        'first_time'   => '09:00:00',
        'incrementing' => true,
    ];

    protected $lotto_name = 'bj28';

    protected $table = 'lotto_beijing8';

    public function collect77()
    {
        $uri      = 'http://508590.com/LuckTwenty/getBaseLuckTwentyList.do?date=&lotCode=10014';
        $client   = new \GuzzleHttp\Client(['timeout' => 5]);
        $response = $client->get($uri);
        $body     = $response->getBody();

        $data  = json_decode($body, true);
        $items = $data['result']['data'];
        $items = array_slice($items, 0, 20);

        foreach ($items as $item) {
            $code = substr($item['preDrawCode'], 0, 59);
            $data = [
                'id'        => $item['preDrawIssue'],
                'open_code' => '  ' . $code,
                'opened_at' => $item['preDrawTime'],
                'time_fix'  => 1,
            ];
            $result = $this->lottoOpen($data);
            dump($data['id'] . ': ' . $result);
        }

        return true;
    }

    public function lottoOpen($data)
    {
        $open_code         = trimAll($data['open_code']);
        $data['open_code'] = substr($open_code, 0, 59);

        $lottoAtFix = function ($time) {
            $time = strtotime($time);
            $diff = floor($time / 300);
            return date('Y-m-d H:i:s', $diff * 300);
        };

        $lotto_at = $lottoAtFix($data['opened_at']);

        $current = $this->remember(1)->find($data['id']);

        $data['status'] = 2;

        if ($current == null) {
            $data['lotto_at'] = $lotto_at;
            $data['status']   = 2;
            $this->create($data);
            return 'create';
        }

        //根据77的采集时间 作为错误值记录
        if ($data['time_fix'] === 1 && $current->lotto_at !== null && $current->lotto_at != $lotto_at) {
            if ($current->lotto_at !== null && $current->lotto_at != $lotto_at) {
                $warning_type = 'warning';
                if ($current->lotto_at > $lotto_at) {
                    $data['status'] = 3;
                    $warning_type   = 'error';
                }
                LottoWarning::lottoAt($warning_type, __CLASS__, $current->id, $lotto_at, $current->lotto_at);
            }
        }

        if ($current->status != 1) {
            return 'status:' . $current->status;
        }

        if ($current->lotto_at === null) {
            $data['lotto_at'] = $lotto_at;
        }

        $current->update($data);

        return 'update';
    }
}
