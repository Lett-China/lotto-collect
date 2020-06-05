<?php
namespace App\Models\LottoModule;

class LottoChart
{
    public function ca28()
    {
        $items = $this->getLottoData('ca28');

        foreach ($items as $item) {
            $he    = $item->win_extend['code_he'];
            $chart = ['win_he' => $he];

            $chart['single'] = $he % 2 == 1;
            $chart['double'] = $he % 2 == 0;
            $chart['big']    = $he >= 14;
            $chart['small']  = $he <= 13;

            $item->chart = $chart;
            $item->makeHidden('win_extend');
        }

        return $items->toArray();
    }

    public function getLottoData($lotto_name)
    {
        $model = LottoUtils::model($lotto_name);
        $items = $model->where('status', '2');
        $items->take(10)->orderBy('id', 'desc');
        $items = $items->get();
        $items->makeHidden(['lotto_name', 'bet_count_down', 'status', 'updated_at', 'opened_at', 'logs', 'mark']);
        return $items;
    }
}
