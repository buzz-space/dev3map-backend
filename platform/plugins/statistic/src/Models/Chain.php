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
        "total_commit",
        "total_contributor",
        "total_issue_solved",
        "total_star",
        "total_fork",
        'last_updated',
        'github_prefix',
        'categories',
        'avatar',
        'website',
        'total_pull_request',
        'total_developer',
        'total_full_time_developer',
        'total_part_time_developer',
        'total_one_time_developer',
        "description",
        "rising_star",
        "ibc_astronaut",
        "seriousness",
    ];


    public
    function repositories()
    {
        return $this->hasMany(Repository::class, "chain");
    }
}
