<?php

namespace Botble\Gallery\Models;

use Botble\Base\Models\BaseModel;

class GalleryMeta extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gallery_meta';

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'images' => 'json',
    ];
}
