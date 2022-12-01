<?php

namespace Botble\Blog\Http\Resources;

use Botble\Base\Models\MetaBox;
use Botble\Blog\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RvMedia;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $lang = $request->input("lang", "en");
        $translation = $this->translation()->whereLangCode($lang)->first();
        $curD = Carbon::createFromTimestamp(strtotime($this->createdAt));
        $diffMi = now()->diffInMinutes($curD);
        $diffH = now()->diffInHours($curD);
        $diffD = now()->diffInDays($curD);
        $diffMo= now()->diffInMonths($curD);
        $diffCurrent = $diffMi > 60 ? (($diffH >= 24)? (($diffD > 31)? $diffMo : $diffD) : $diffH) : $diffMi;
        $diffType = $diffMi > 60 ? (($diffH >= 24)? (($diffD > 31)? "month" : "day") : "hour") : "minute";

        $seoTitle = $this->name;
        $seoDescription = $this->description;
        $meta = MetaBox::where([
            ["meta_key", "seo_meta"],
            ["reference_id", $this->id],
            ["reference_type", Post::class]
        ])->first();
        if ($meta){
            $array = $meta->meta_value;
            $seoTitle = $array[0]["seo_title"];
            $seoDescription = $array[0]["seo_description"];
        }

        return [
            'id'          => $this->id,
            'title'        => ($lang != "en")? (($translation)? $translation->name : $this->name) : $this->name,
            'slug'        => $this->slug,
            'description' => ($lang != "en")? (($translation)? $translation->description : $this->description) : $this->description,
            'image'       => $this->image ? RvMedia::url($this->image) : null,
            'category'  => CategoryResource::collection($this->categories),
            'tag'        => $this->tags()->pluck("name")->toArray(),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'ago' => $diffCurrent,
            'agoType' => $diffType,
            'content' => $this->content,
            'author'      => collect([
                "username" => $this->author->username ?? "Unknown",
                "avatar" => $this->author->avatar_url ?? RvMedia::getDefaultImage(),
                "description" => $this->author->description ?? ""
            ]),
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'related_post' => ListPostResource::collection($this->posts)
        ];

    }
}
