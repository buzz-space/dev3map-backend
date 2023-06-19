<?php

namespace Botble\Banner\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class BannerTranslation extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'banners_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'subtitle',
        'description',
        'lang_code',
        'banners_id',
    ];


}
