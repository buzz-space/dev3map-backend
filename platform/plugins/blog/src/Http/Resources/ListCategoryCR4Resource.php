<?php

namespace Botble\Blog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListCategoryCR4Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
//            'children'    => CategoryResource::collection($this->children),
//            'parent'      => new CategoryResource($this->parent),
        ];
    }
}
