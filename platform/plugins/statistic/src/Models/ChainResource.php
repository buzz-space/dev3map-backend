<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class ChainResource extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chain_resources';

    /**
     * @var array
     */
    protected $fillable = [
        "chain",
        "name",
        "refer_ici",
        "category",
        "image",
        "created_date",
    ];
}
