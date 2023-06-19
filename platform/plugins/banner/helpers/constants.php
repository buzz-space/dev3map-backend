<?php

use Botble\Banner\Repositories\Interfaces\BannerInterface;

if (!defined('BANNER_MODULE_SCREEN_NAME')) {
    define('BANNER_MODULE_SCREEN_NAME', 'banner');
}

function getBannerOption($option){
    return app(BannerInterface::class)->advancedGet($option);
}
