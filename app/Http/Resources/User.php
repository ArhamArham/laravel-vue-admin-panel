<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'email'=>$this->email,
            'role'=>$this->role ? $this->role->name :'',
            'photo'=>$this->profile->photo ? $this->profile->photo :'images/no_image.png',
            'created_at'=>$this->created_at->format('Y-M-D H:i:s'),
            'updated_at'=>$this->updated_at->format('Y-M-D H:i:s'),
        ];
    }
}
