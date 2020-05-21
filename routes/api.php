<?php
use Illuminate\Support\Facades\Route;
Route::auto('dev', 'DevController');
Route::auto('keno-de/open', 'KenoDe\OpenController');
Route::auto('keno-de/web', 'KenoDe\WebController');
Route::get('/lotto/{lotto_name}/opened-log', 'LottoController@openedLog');
Route::get('/bitcoin-collect', 'LottoController@bitcoinCollect');
