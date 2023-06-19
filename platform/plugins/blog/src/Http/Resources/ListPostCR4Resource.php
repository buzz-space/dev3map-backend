<?php

namespace Botble\Blog\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RvMedia;

class ListPostCR4Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $curD = Carbon::createFromTimestamp(strtotime($this->createdAt));
        $diffMi = now()->diffInMinutes($curD);
        $diffH = now()->diffInHours($curD);
        $diffD = now()->diffInDays($curD);
        $diffMo= now()->diffInMonths($curD);
        $diffCurrent = $diffMi > 60 ? (($diffH >= 24)? (($diffD > 31)? $diffMo : $diffD) : $diffH) : $diffMi;
        $diffType = $diffMi > 60 ? (($diffH >= 24)? (($diffD > 31)? "month" : "day") : "hour") : "minute";

        $type = [];
        if (!empty($this->category)){
            foreach ($this->category as $item){
                if ($item->slug == "markets")
                    $type[] = "NFT";
                if ($item->slug == "nfts-metaverse")
                    $type[] = "Metaverse";
            }
        }
        if (Carbon::createFromTimestamp(strtotime($this->createdAt))->day == now()->day)
            $type[] = "Web 3.0";
        return [
            'id' => $this->_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->summary,
            'image' => $this->image,
            'category' => $this->category,
            'tag' => $this->tag,
            'created_at' => $this->createdAt,
            'ago' => $diffCurrent,
            'agoType' => $diffType,
            'updated_at' => $this->updatedAt,
            'author' => collect([
                "username" => $this->author->username,
                "avatar" => $this->author->avatar
            ]),
            'type' => $type,
        ];
    }
}
