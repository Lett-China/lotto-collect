<?php
Route::post('auth/login', 'AuthController@login'); //管理员登录
Route::get('config/get', 'ConfigController@get');
Route::get('config/mapping', 'ConfigController@mapping');

Route::middleware('jwt:admin')->group(function () {
    Route::full('auth', 'AuthController');
    Route::full('administrator', 'AdministratorController');
    Route::full('article/{cat_id}', 'ArticleController');
    Route::full('article/cat', 'ArticleCategoryController');
    Route::full('notice', 'NoticeController');
    Route::full('member', 'MemberController');
    Route::get('member/balanceLog', 'MemberController@balanceLog');
    Route::get('member/next-level', 'MemberController@nextLevel');
    Route::get('member/check-ip', 'MemberController@checkIP');
    Route::post('member/wallet/update', 'MemberController@walletUpdate');
    Route::post('image/create', 'CommonController@imageCreate');

    Route::full('option/focus', 'OptionFocusController');
    Route::full('option', 'OptionController');
    Route::post('option/update/patch', 'OptionController@updatePatch');

    Route::full('contact', 'ContactMessageController');
    Route::full('single-page/{type}', 'SinglePageController');

    Route::full('lotto/{lotto_name}/config', 'LottoConfigController');
    Route::full('lotto/{lotto_name}/data', 'LottoDataController');
    Route::post('lotto/{lotto_name}/data/control', 'LottoDataController@control');
    Route::auto('lotto/bet-log', 'Admin\BetLogController');
    Route::auto('lotto/error', 'Admin\LottoErrorController');
    // Route::full('lotto/canada28', 'Canada28Controller');
    Route::auto('lotto/import', 'Admin\LottoImportController');
    Route::auto('service', 'Admin\ServiceController');
    Route::auto('recharge', 'Admin\RechargeController');
    Route::auto('withdraw', 'Admin\WithdrawController');
    Route::auto('app-stats', 'Admin\AppStatsController');
});
