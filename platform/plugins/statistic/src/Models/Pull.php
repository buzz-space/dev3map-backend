<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Pull extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'repository_pulls';

    /**
     * @var array
     */
    protected $fillable = [
        "pull_id",
        "repo",
        "chain",
        "author",
        "status",
    ];
}
