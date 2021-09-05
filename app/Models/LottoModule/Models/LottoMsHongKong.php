<?php
namespace App\Models\LottoModule\Models;

use Watson\Rememberable\Rememberable;
use App\Models\LottoModule\LottoUtils;
use Illuminate\Database\Eloquent\Model;

class LottoMsHongKong extends Model
{
    use Rememberable;

    public $incrementing = false;

    public $rememberCacheTag = 'lotto_ms_hongkong';

    public $timestamps = false;

    protected $connection = 'lotto_data';

    protected $fillable = ['id', 'open_code', 'lotto_at', 'opened_at', 'mark', 'status', 'extend', 'control', 'logs'];

    protected $hidden = ['extend', 'control', 'logs'];

    protected $lotto_name = 'mshk';

    protected $table = 'lotto_ms_hongkong';

    public function lottoCreate()
    {
        $count = $this->where('status', 1)->where('lotto_at', '>', date('Y-m-d H:i'))->count();
        if ($count >= 2) {
            return 'lotto limit';
        }

        $last_lotto = $this->orderBy('id', 'desc')->first();

        $date_offset = [
            '2' => 172800,
            '4' => 172800,
            '6' => 259200,
        ];

        $week = date('w', strtotime($last_lotto->lotto_at));

        $next_date = date('Y-m-d H:i:s', strtotime($last_lotto->lotto_at) + $date_offset[$week]);

        $data = [
            'id'       => $last_lotto->id + 1,
            'lotto_at' => $next_date,
        ];

        $this->create($data);
    }

    public function lottoOpen($data)
    {
        $lotto_at       = $data['opened_at'];
        $current        = $this->remember(1)->find($data['id']);
        $data['status'] = 2;

        if ($current == null) {
            $data['lotto_at'] = $lotto_at;
            $data['status']   = 2;
            $this->create($data);
            return 'create';
        }

        if ($current->status != 1) {
            return 'status:' . $current->status;
        }

        if ($current->lotto_at !== null && $current->lotto_at != $lotto_at) {
            $warning_type = 'warning';
            if ($current->lotto_at > $lotto_at) {
                $data['status'] = 3;
                $warning_type   = 'error';
            } else {
                // $data['lotto_at'] = $lotto_at;
                // $this->where('id', '>', $current->id)->delete();
            }
            LottoWarning::lottoAt($warning_type, __CLASS__, $current->id, $lotto_at, $current->lotto_at);
        }

        $current->update($data);

        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);
        return 'update';
    }

    public function thirdCollect()
    {
        $uri = 'https://like.luckaacc.com/hall/pc/lottery/get-recent-records.html?code=hklhc&pageSize=3';

        $options = [
            'headers' => [
                'x-requested-with' => 'XMLHttpRequest',
            ],

        ];
        $client   = new \GuzzleHttp\Client(['timeout' => 5]);
        $response = $client->get($uri, $options);
        $data     = json_decode($response->getBody(), true);

        if ($data['error'] !== 0) {
            return '采集错误';
        }

        $data = $data['data'];

        foreach ($data as $item) {
            $item = [
                'id'        => $item['expect'],
                'open_code' => $item['openCode'],
                'opened_at' => date('Y-m-d H:i:s', $item['openTime'] / 1000),
            ];

            $this->lottoOpen($item);
        }

        return true;
    }
}
