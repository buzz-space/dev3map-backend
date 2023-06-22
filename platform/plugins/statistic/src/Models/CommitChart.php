<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class CommitChart extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'commit_chart';

    /**
     * @var array
     */
    protected $fillable = [
        "chain",
        "week",
        "month",
        "year",
        "total_commit",
        "total_additions",
        "total_deletions",
        "total_fork_commit",
    ];

}
