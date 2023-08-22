<?php

namespace Botble\Statistic\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Chain extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chains';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'last_updated',
        'github_prefix',
        'symbol',
        'categories',
        'avatar',
        "subscribers",
        'website',
        "rising_star",
        "ibc_astronaut",
        "seriousness",
        "commit_rank",
        "pull_rank",
        "dev_rank",
        "issue_rank",
        "fork_rank",
        "star_rank",
        "pr_rank",
        "is_repo",
    ];

    public function stats()
    {
        return $this->hasMany(ChainInfo::class, "chain");
    }

    public function repositories()
    {
        return $this->hasMany(Repository::class, "chain");
    }

    public function info()
    {
        return $this->hasMany(ChainInfo::class, "chain");
    }
}
