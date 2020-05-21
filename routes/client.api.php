<?php

Route::get('option/get', 'OptionController@get');

Route::prefix('auth')->group(function () {
    Route::post('register', 'UserAuthController@register'); //用户注册
    Route::post('login', 'UserAuthController@login'); //用户登录
    Route::post('password', 'UserAuthController@passwordUpdate'); //用户重置密码
    Route::get('nickname', 'UserAuthController@randNickname'); //注册时生成随机昵称
    Route::post('trial', 'UserAuthController@trial'); // 注册试玩账户
});
Route::post('sms/{privateMethod}', 'SMSController@get');

Route::full('article/{cat_id}', 'ArticleController');
Route::get('article/get', 'ArticleController@get');
Route::full('page/{type}', 'SinglePageController');
Route::get('lotto/{lotto_name}/united', 'LottoController@united');
Route::get('lotto/{lotto_name}/config', 'LottoController@config');
Route::get('lotto/{lotto_name}/last', 'LottoController@last');
Route::get('lotto/{lotto_name}/newest', 'LottoController@newest');
Route::post('lotto/{lotto_name}/betting', 'LottoController@betting');
Route::get('lotto/{lotto_name}/betLog', 'LottoController@betLog');
Route::post('lotto/{lotto_name}/send-message', 'LottoController@sendMessage');

Route::middleware('jwt:users')->group(function () {
    Route::full('user', 'UserAuthController');
    Route::post('user/safe-word/check', 'UserAuthController@safeWordCheck');
    Route::post('user/avatar/update', 'UserAuthController@avatarUpdate');
    Route::full('contact', 'ContactMessageController');
    Route::auto('wallet', 'Client\UserWalletController');
    Route::auto('service', 'Client\ServiceController');
    Route::auto('bank-card', 'Client\BankCardController');
    Route::auto('withdraw', 'Client\WithdrawController');
    Route::auto('recharge', 'Client\RechargeController');
    Route::auto('statistics', 'Client\StatisticsController');
    Route::auto('user/reference', 'Client\UserReferenceController');
});
