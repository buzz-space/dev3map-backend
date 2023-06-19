<?php
Route::group([
    'middleware' => 'api',
    'prefix'     => 'api/v1',
    'namespace'  => 'Botble\Setting\Http\Controllers\API',
], function () {
    Route::get("setting", "SettingController@getSetting");
});
