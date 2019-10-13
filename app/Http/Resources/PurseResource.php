<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PurseResource extends Resource
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
            'client' => new ClientResource($this->whenLoaded('client')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'balance' => (float)$this->balance,
        ];
    }
}
