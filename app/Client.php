<?php

namespace App;

use App\BaseModel;

class Client extends BaseModel
{
    protected $fillable = [
        'name', 'country', 'city',
    ];

    public function purse() {
        return $this->hasOne(Purse::class, 'client_id');
    }
}
