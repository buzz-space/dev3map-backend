<?php

namespace Botble\Statistic\Models;

use Botble\Base\Models\BaseModel;

class StatisticTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'statistics_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'statistics_id',
        'name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
