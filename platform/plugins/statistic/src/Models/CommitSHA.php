<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class CommitSHA extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'commit_sha';

    /**
     * @var array
     */
    protected $fillable = [
        "commit_id",
        "sha",
    ];

}
