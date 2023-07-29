<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Developer extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'developers';

    /**
     * @var array
     */
    protected $fillable = [
        'chain',
        'day',
        "author",
        "total_commit",
        "total_developer",
        'total_one_time',
        'total_part_time',
        'total_full_time',
        'one_time',
        'part_time',
        'full_time',
    ];

    public function repositories()
    {
        return $this->hasMany(Repository::class, "chain");
    }
}
