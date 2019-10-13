<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CurrencyResource extends Resource
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
            'code' => $this->code,
            'name' => $this->name,
            'quote' => (float)$this->quote,
        ];
    }
}
