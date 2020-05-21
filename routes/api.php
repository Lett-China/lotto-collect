<?php
Route::middleware('jwt:users')->group(function () {
});
Route::auto('dev', 'DevController');
Route::auto('keno-de/open', 'KenoDe\OpenController');
Route::auto('keno-de/web', 'KenoDe\WebController');
