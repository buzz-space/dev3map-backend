<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Issue extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'repository_issues';

    /**
     * @var array
     */
    protected $fillable = [
        "issue_id",
        "repo",
        "chain",
        "creator",
        "open_date",
        "close_date",
        "total_minute",
    ];
}
