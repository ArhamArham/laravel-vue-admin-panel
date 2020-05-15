<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
class Category extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'description'=>Str::limit($this->description, 250),
            'created_at'=>$this->created_at->format('Y-M-D H:i:s'),
            'updated_at'=>$this->updated_at->format('Y-M-D H:i:s'),
        ];
    }
}
