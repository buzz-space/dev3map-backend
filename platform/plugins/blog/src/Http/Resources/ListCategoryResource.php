<?php

namespace Botble\Blog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListCategoryResource extends JsonResource
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
            'name'        => ($lang != "en")? (($translation)? $translation->name : $this->name) : $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
        ];
    }
}
