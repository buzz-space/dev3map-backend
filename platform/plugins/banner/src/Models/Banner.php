<?php

namespace Botble\Banner\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Banner extends BaseModel
{
    const POSITION_AZ_GUIDE = "az-guide";
    const POSITION_SLIDER = "slider";

    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'banners';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'subtitle',
        'link',
        'status',
        'position',
        'order',
        'image',
        'background_color',
        'description',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    public function translation()
    {
        return $this->hasOne(BannerTranslation::class, "banners_id");
    }
}
