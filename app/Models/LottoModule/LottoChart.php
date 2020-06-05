<?php
namespace App\Models\LottoModule;

class LottoChart
{
    public function ca28()
    {
        $items = $this->getLottoData('ca28');

        foreach ($items as $item) {
            $he = $item->win_extend['code_he'];
            $chart = ['win_he' => $he];

            $chart['single'] = $he % 2 == 1;
            $chart['double'] = $he % 2 == 0;
            $chart['big'] = $he >= 14;
            $chart['small'] = $he <= 13;
            $chart['zhong'] = $he >= 10 && $he <= 17 ? true : false;
            $chart['bian'] = !$chart['zhong'];
            $chart['wei_big'] = substr($he, -1) >= 5 ? true : false;
            $chart['wei_small'] = !$chart['wei_big'];
            $chart['yu_3'] = $he % 3;
            $chart['yu_4'] = $he % 4;
            $chart['yu_5'] = $he % 5;
            $item['lotto_at'] = date("m-d H:i:s", strtotime($item['lotto_at']));
            $item->chart = $chart;
            $item->makeHidden('win_extend');
        }

        return $items->toArray();
    }

    public function getLottoData($lotto_name)
    {
        $limit = request()->limit ?: 100;
        $model = LottoUtils::model($lotto_name);
        $items = $model->where('status', '2');
        $items->take($limit)->orderBy('id', 'desc');
        $items = $items->get();
        $items->makeHidden(['lotto_name', 'bet_count_down', 'status', 'updated_at', 'opened_at', 'logs', 'mark']);
        return $items;
    }
}
