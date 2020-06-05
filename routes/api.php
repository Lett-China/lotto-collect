<?php
use Illuminate\Support\Facades\Route;
Route::auto('dev', 'DevController');
Route::get('/lotto/{lotto_name}/open-log', 'LottoController@openLog');
Route::get('/bitcoin-collect', 'LottoController@bitcoinCollect');
Route::get('/lotto/chart', 'LottoChartController@index');
