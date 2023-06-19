<?php

Route::group(['namespace' => 'Botble\Statistic\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'statistics', 'as' => 'statistic.'], function () {
            Route::resource('', 'StatisticController')->parameters(['' => 'statistic']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'StatisticController@deletes',
                'permission' => 'statistic.destroy',
            ]);
        });
    });

});
