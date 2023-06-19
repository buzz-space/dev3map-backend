<?php

Route::group(['namespace' => 'Botble\Banner\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'banners', 'as' => 'banner.'], function () {
            Route::resource('', 'BannerController')->parameters(['' => 'banner']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'BannerController@deletes',
                'permission' => 'banner.destroy',
            ]);
        });
    });

});
