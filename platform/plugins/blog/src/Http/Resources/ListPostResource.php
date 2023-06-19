<?php

namespace Botble\Blog\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RvMedia;

class ListPostResource extends JsonResource
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

        return [
            'id'          => $this->id,
            'title'        => ($lang != "en")? (($translation)? $translation->name : $this->name) : $this->name,
            'slug'        => $this->slug,
            'description' => ($lang != "en")? (($translation)? $translation->description : $this->description) : $this->description,
            'image'       => $this->image ? RvMedia::url($this->image) : null,
            'category'  => CategoryResource::collection($this->categories),
//            'tag'        => $this->tags()->pluck("name")->toArray(),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'author'      => collect([
                "username" => $this->author->username ?? "Unknown",
                "avatar" => $this->author->avatar_url ?? RvMedia::getDefaultImage()
            ]),
            'time' => date("d/m/Y", strtotime($this->created_at)),
        ];
    }
}
