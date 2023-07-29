<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class ChainInfo extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chain_info';

    /**
     * @var array
     */
    protected $fillable = [
        'chain',
        "total_commits",
        "total_issue_solved",
        "total_pull_merged",
        "total_star",
        "total_fork",
        "full_time_developer",
        "part_time_developer",
        "full_time",
        "part_time",
        "total_repository",
        "range"
    ];

    protected $appends = ["total_developer"];

    public function getTotalDeveloperAttribute()
    {
        return $this->full_time_developer + $this->part_time_developer;
    }
}
