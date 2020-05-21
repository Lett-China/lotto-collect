<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Packages\Utils\LettCurl;
use App\Packages\Utils\PushEvent;

class Gd28CheckCommand extends Command
{
    protected $description = 'gd28 check';

    protected $signature = 'gd28:check';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $curl = LettCurl::https('https://www.36yt.com/new/auto')->get()->toArray();
        $this->info('gd28 check start');
        dump($curl);
        if ($curl['total'] === true) {
            $message = [
                'message' => '广东28 - 库银异常',
                'desc'    => '请联系管理员即时处理',
                'audio'   => 'gdErrorTotal',
            ];
            PushEvent::name('notify')->toUser(10000000)->data($message);
        }

        if (count($curl['agent_card']) > 0) {
            $message = [
                'message' => '广东28 - 代理兑卡异常',
                'desc'    => '请联系管理员即时处理',
                'audio'   => 'gdErrorAgent',
            ];
            PushEvent::name('notify')->toUser(10000000)->data($message);
        }

        return $this->info('gd28 check success');
    }
}
