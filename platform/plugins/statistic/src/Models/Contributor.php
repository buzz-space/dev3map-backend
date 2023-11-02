<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Contributor extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'repository_contributors';

    /**
     * @var array
     */
    protected $fillable = [
        "chain",
        "repo",
        "name",
        "login",
        "description",
        "avatar",
    ];
}
