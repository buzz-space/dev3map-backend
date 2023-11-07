<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class DeveloperStatistic extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contributor_statistic';

    /**
     * @var array
     */
    protected $fillable = [
        "range",
        "contributor_id",
        "total_commit",
        "total_pull_request",
        "total_pull_merged",
        "total_issue",
        "merge_ratio",
    ];
}
