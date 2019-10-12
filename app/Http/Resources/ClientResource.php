<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClientResource extends Resource
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
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'patronomic' => $this->patronomic,
            'country' => $this->country,
            'city' => $this->city,
            'purse' => new PurseResource($this->whenLoaded('purse')),
        ];
    }
}
