<?php

namespace Botble\Blog\Http\Resources;

use Botble\Blog\Models\PostComment;
use Botble\Blog\Models\PostLike;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RvMedia;

class ListCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $curD = Carbon::createFromTimestamp(strtotime($this->created_at));
        $diffMi = now()->diffInMinutes($curD);
        $diffH = now()->diffInHours($curD);
        $diffD = now()->diffInDays($curD);
        $diffMo= now()->diffInMonths($curD);
        $diffCurrent = $diffMi > 60 ? (($diffH >= 24)? (($diffD > 31)? $diffMo : $diffD) : $diffH) : $diffMi;
        $diffType = $diffMi > 60 ? (($diffH >= 24)? (($diffD > 31)? "month" : "day") : "hour") : "minute";

        $user = $this->user()->select("id", "avatar", "name")->first();

        return [
            "id" => $this->id,
            "post_id" => $this->post_id,
            "post_cr4_id" => $this->post_cr4_id,
            "user_id" => $this->user_id,
            "comment_id" => $this->comment_id,
            "comment" => $this->comment,
            "likes" => $this->likes,
            "is_like" => $this->is_like ?? false,
            "reply_count" => PostComment::where("comment_id", $this->id)->count(),
            "ago" => $diffCurrent,
            "agoType" => $diffType,
            "user" => [
                "id" => $user->id ?? "",
                "image" => ($user && $user->avatar) ? RvMedia::url($user->avatar) : null,
                "name" => $user ? $user->name : null
            ],
            'owned' => $this->owned
        ];
    }
}
