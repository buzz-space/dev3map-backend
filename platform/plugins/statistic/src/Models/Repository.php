<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Repository extends BaseModel
{
    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'repositories';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        "total_commit",
        "total_contributor",
        "total_issue_solved",
        "pull_request_closed",
        "total_star",
        "total_fork",
        "subscribers",
        'chain',
        'is_fork',
        'github_prefix',
        "created_date",
    ];

    public function chain()
    {
        return $this->belongsTo(Chain::class, "chain");
    }
}
