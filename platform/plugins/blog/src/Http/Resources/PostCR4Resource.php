<?php

namespace Botble\Blog\Http\Resources;

use Botble\Blog\Models\PostComment;
use Botble\Blog\Models\PostLike;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RvMedia;

class PostCR4Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $type = [];
        if ($this->isTrending ?? null)
            $type[] = "hot";
        if (!empty($this->category)){
            foreach ($this->category as $item){
                if ($item->slug == "nfts-metaverse")
                    $type[] = "metaverse";
                if ($item->slug == "markets")
                    $type[] = "nft";
            }
        }
        if (Carbon::createFromTimestamp(strtotime($this->createdAt))->day == now()->day)
            $type[] = "web-3";
        return [
            'id'           => $this->_id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'description'  => $this->summary,
            'image'        => $this->image,
            'category'     => $this->category,
            'tag'          => $this->tag,
            'created_at'   => $this->createdAt,
            'diff_in_hour' => now()->diffInHours(Carbon::createFromTimestamp(strtotime($this->createdAt))),
            'updated_at'   => $this->updatedAt,
            'author'       => collect([
                "username" => $this->author->username,
                "avatar"   => $this->author->avatar
            ]),
            'type' => $type,
            'content' => $this->bodyFull,
            'write_day' => Carbon::createFromTimestamp(strtotime($this->createdAt))->day,
            'write_month' => Carbon::createFromTimestamp(strtotime($this->createdAt))->month,
            'write_hour' => Carbon::createFromTimestamp(strtotime($this->createdAt))->format("h:m a"),
            'total_comment' => PostComment::where("post_cr4_id", $this->_id)->count(),
            'total_like' => PostLike::where("post_cr4_id", $this->_id)->count(),
            'next_slug' => $this->next_slug
        ];

    }
}
