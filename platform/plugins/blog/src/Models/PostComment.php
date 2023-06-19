<?php

namespace Botble\Blog\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Customers\Models\Customers;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Html;
use Illuminate\Support\Collection;

class PostComment extends BaseModel
{
    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_comments';

    /**
     * The date fields
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "post_id",
        "post_cr4_id",
        "user_id",
        "comment",
        "comment_id",
        "likes",
        "reports"
    ];

    public function user()
    {
        return $this->belongsTo(Customers::class, "user_id");
    }

    public function reply()
    {
        return $this->hasMany(PostComment::class, "comment_id");
    }

    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($comment) {
            foreach ($comment->reply()->get() as $child) {
                $child->delete();
            }
        });
    }
}
