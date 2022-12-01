<?php

namespace Botble\Banner\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RvMedia;

class BannerResource extends JsonResource
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
            'id'                => $this->id,
            'name'              => ($lang != "en")? (($translation)? $translation->name : $this->name) : $this->name,
            'name_vi'           => ($lang != "en")? (($translation)? $translation->name : $this->name) : $this->name,
            'subtitle'          => ($lang != "en")? (($translation)? $translation->subtitle : $this->subtitle) : $this->subtitle,
            'description'       => ($lang != "en")? (($translation)? $translation->description : $this->description) : $this->description,
            'description_vi'    => ($lang != "en")? (($translation)? $translation->description : $this->description) : $this->description,
            'slug'              => $this->slug,
            'link'              => $this->link,
            'background_color'  => $this->background_color,
            'image'             => ($this->image)? RvMedia::url($this->image) : RvMedia::getDefaultImage(),
        ];
    }
}
