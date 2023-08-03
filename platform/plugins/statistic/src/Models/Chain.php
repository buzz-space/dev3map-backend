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
        return $this->hasOne(ChainInfo::class, "chain");
    }
}
