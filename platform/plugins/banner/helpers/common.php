<?php

use Botble\Banner\Models\Banner;

function getBannerPosition(){
    return [
        Banner::POSITION_AZ_GUIDE => "A-Z Guide",
        Banner::POSITION_SLIDER => ucfirst(Banner::POSITION_SLIDER),
    ];
}
