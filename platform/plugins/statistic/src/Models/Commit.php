<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Commit extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'commits';

    /**
     * @var array
     */
    protected $fillable = [
        'chain',
        "repo",
        "branch",
        "exact_date",
        "author_list",
        "total_commit",
        "total_fork_commit",
        "additions",
        "deletion",
        "full_time",
        "part_time",
        "total_full_time",
        "total_part_time",
        "one_time",
    ];

}
