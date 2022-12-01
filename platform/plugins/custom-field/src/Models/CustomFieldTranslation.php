<?php

namespace Botble\CustomField\Models;

use Botble\Base\Models\BaseModel;

class CustomFieldTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'custom_fields_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'custom_fields_id',
        'value',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
