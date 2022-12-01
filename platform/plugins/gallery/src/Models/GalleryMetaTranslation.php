<?php

namespace Botble\Gallery\Models;

use Botble\Base\Models\BaseModel;

class GalleryMetaTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gallery_meta_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'gallery_meta_id',
        'images',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'images' => 'json',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
